<template>
  <el-table
    :data="tableData"
    style="width: 100%"
    v-loading="loading"
    :default-sort="{ prop: 'created_at', order: 'descending' }"
    scroll-x
  >
    <el-table-column prop="id" :label="$t('users.id')" width="80" sortable>
      <template #default="scope">
        <span
          style="
            font-family: var(--voa-mono, monospace);
            font-size: 12px;
            font-weight: 700;
            color: var(--el-color-primary);
          "
        >
          {{ formatRequestId(scope.row.id) }}
        </span>
      </template>
    </el-table-column>

    <el-table-column
      prop="requester_name"
      :label="$t('travelRequest.requesterName')"
      min-width="180"
      show-overflow-tooltip
    />

    <el-table-column
      prop="destination"
      :label="$t('travelRequest.destination')"
      min-width="180"
      show-overflow-tooltip
    />

    <el-table-column :label="$t('travelRequest.dates')" min-width="200">
      <template #default="scope">
        <div class="date-range-cell">
          {{ formatDateLong(scope.row.departure_date) }} →
          {{ formatDateLong(scope.row.return_date) }}
        </div>
      </template>
    </el-table-column>

    <el-table-column prop="travel_type" :label="$t('travelRequest.travelType')" width="130">
      <template #default="scope">
        <span v-if="scope.row.travel_type" style="display: flex; align-items: center; gap: 6px">
          <el-icon :style="{ color: getTravelTypeColor(scope.row.travel_type) }">
            <component :is="travelTypeIcon(scope.row.travel_type)" />
          </el-icon>
          {{ $t('travelRequest.travelType_' + scope.row.travel_type) }}
        </span>
        <span v-else class="text-muted">-</span>
      </template>
    </el-table-column>

    <el-table-column prop="status" :label="$t('dashboard.status')" width="120" sortable>
      <template #default="scope">
        <el-tag :type="getStatusType(scope.row.status)" size="small">
          {{ translateStatus(scope.row.status) }}
        </el-tag>
      </template>
    </el-table-column>

    <el-table-column
      v-if="showApprover"
      prop="approved_by"
      :label="$t('travelRequest.approvedBy')"
      min-width="160"
      show-overflow-tooltip
    >
      <template #default="scope">
        <span v-if="scope.row.approved_by">{{ scope.row.approved_by?.name }}</span>
        <span v-else class="text-muted">-</span>
      </template>
    </el-table-column>

    <el-table-column :label="$t('users.actions')" width="200" fixed="right">
      <template #default="scope">
        <el-tooltip :content="$t('travelRequest.tooltipView')" placement="top">
          <el-button type="primary" :icon="View" circle size="small" @click="handleView(scope.row)" />
        </el-tooltip>
        <el-tooltip :content="$t('travelRequest.tooltipEdit')" placement="top">
          <el-button
            v-if="canEdit(scope.row)"
            type="info"
            :icon="Edit"
            circle
            size="small"
            @click="handleEdit(scope.row)"
          />
        </el-tooltip>
        <el-tooltip :content="$t('travelRequest.tooltipApprove')" placement="top">
          <el-button
            v-if="canApprove(scope.row)"
            type="success"
            :icon="CircleCheck"
            circle
            size="small"
            @click="handleApprove(scope.row)"
          />
        </el-tooltip>
        <el-tooltip :content="$t('travelRequest.tooltipCancel')" placement="top">
          <el-button
            v-if="canCancel(scope.row)"
            type="warning"
            :icon="CircleClose"
            circle
            size="small"
            @click="handleCancel(scope.row)"
          />
        </el-tooltip>
      </template>
    </el-table-column>
  </el-table>

  <!-- Cancel dialog -->
  <el-dialog v-model="cancelDialogVisible" :title="$t('travelRequest.cancelTitle')" width="450px" align-center>
    <p>{{ $t('travelRequest.cancelConfirmMessage') }}</p>
    <el-form-item :label="$t('travelRequest.cancelReasonLabel')" required style="margin-top: 16px;">
      <el-input
        v-model="cancelReason"
        type="textarea"
        :rows="3"
        :placeholder="$t('travelRequest.cancelReasonPlaceholder')"
      />
    </el-form-item>
    <template #footer>
      <el-button @click="cancelDialogVisible = false">{{ $t('common.cancel') }}</el-button>
      <el-button type="warning" @click="confirmCancel" :loading="cancelling" :disabled="!cancelReason.trim()">
        {{ $t('common.confirm') }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  View,
  Edit,
  CircleClose,
  CircleCheck,
  Promotion,
  Van,
  MapLocation,
  House,
} from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const props = defineProps({
  data: { type: Array, required: true },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['status-change', 'cancel', 'view', 'approve', 'edit'])

const authStore = useAuthStore()

const cancelDialogVisible = ref(false)
const cancelling = ref(false)
const selectedRequest = ref(null)
const cancelReason = ref('')

const tableData = computed(() => props.data)
const showApprover = computed(() => authStore.isApprover)

const canApprove = (row) => {
  if (!row) return false
  // Apenas aprovadores podem aprovar
  if (!authStore.isApprover) return false
  // Não pode aprovar pedido próprio
  if (row.user_id === authStore.user?.id) return false
  // Apenas pedidos solicitados podem ser aprovados
  return row.status === 'requested'
}

const canCancel = (row) => {
  if (!row) return false
  // Não pode cancelar pedidos vencidos
  if (row.status === 'expired') return false
  // Apenas pedidos solicitados ou aprovados podem ser cancelados
  if (!['requested', 'approved'].includes(row.status)) return false
  // Verifica se tem a propriedade can_be_cancelled
  if (row.can_be_cancelled === false) return false
  // Apenas aprovadores podem cancelar
  if (!authStore.isApprover) return false
  // Não pode cancelar pedido próprio
  if (row.user_id === authStore.user?.id) return false
  // Verifica se a data de partida ainda não passou
  if (row.departure_date) {
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const departureDate = new Date(row.departure_date)
    if (departureDate < today) return false
  }
  return true
}

const canEdit = (row) => {
  if (!row) return false
  // Apenas pedidos solicitados podem ser editados
  if (row.status !== 'requested') return false
  // Apenas aprovadores podem editar
  if (!authStore.isApprover) return false
  // Não pode editar pedido próprio
  if (row.user_id === authStore.user?.id) return false
  return true
}

const handleEdit = (row) => {
  emit('edit', row)
}

const canChangeStatus = (row) => {
  if (!row) return false
  // Não permitir mudança de status se foi cancelado pelo sistema
  if (row.status === 'cancelled' && isCancelledBySystem(row)) return false
  return authStore.isApprover && row.user_id !== authStore.user?.id
}

// Verificar se o pedido foi cancelado pelo sistema (usuário desativado)
const isCancelledBySystem = (row) => {
  if (!row || row.status !== 'cancelled') return false
  const systemPatterns = ['usuário desativado', 'usuário excluído', 'Usuário desativado', 'Usuário excluído']
  return systemPatterns.some(pattern => 
    row.cancel_reason?.includes(pattern)
  )
}

const getStatusType = (status) => {
  const types = { requested: 'warning', approved: 'success', cancelled: 'danger', expired: 'info' }
  return types[status] || ''
}

const translateStatus = (status) => {
  const translations = {
    requested: t('status.requested'),
    approved: t('status.approved'),
    cancelled: t('status.cancelled'),
    expired: t('status.expired'),
  }
  return translations[status] || status
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatDateLong = (date) => {
  if (!date) return '-'
  const d = typeof date === 'string' && date.includes('T') ? date.split('T')[0] : date
  const dateObj = new Date(d + 'T12:00:00')
  const day = dateObj.getDate().toString().padStart(2, '0')
  const month = dateObj.toLocaleDateString('pt-BR', { month: 'short' })
  return `${day} de ${month}`
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('pt-BR')
}

const formatRequestId = (id) => {
  if (!id) return '-'
  return `VG-${String(id).padStart(3, '0')}`
}

const travelTypeIcon = (type) => {
  return { aereo: Promotion, onibus: Van, carro: MapLocation, hotel: House }[type] || Van
}

const getTravelTypeColor = (type) => {
  return (
    {
      aereo: 'var(--travel-type-aereo)',
      onibus: 'var(--travel-type-onibus)',
      carro: 'var(--travel-type-carro)',
      hotel: 'var(--travel-type-hotel)',
    }[type] || 'var(--el-color-primary)'
  )
}

const handleApprove = (row) => {
  selectedRequest.value = row
  emit('approve', row.id)
}

const handleCancel = (row) => {
  selectedRequest.value = row
  cancelReason.value = ''
  cancelDialogVisible.value = true
}

const confirmCancel = async () => {
  cancelling.value = true
  await emit('cancel', { id: selectedRequest.value.id, reason: cancelReason.value })
  cancelling.value = false
  cancelReason.value = ''
  cancelDialogVisible.value = false
}

const handleStatusChangeInline = async (row, newStatusValue) => {
  await emit('status-change', row.id, newStatusValue)
}

const handleView = (row) => {
  emit('view', row)
}
</script>

<style scoped>
:deep(.el-table) {
  font-size: 14px;
}

.date-range-cell {
  white-space: normal;
  line-height: 1.5;
}

@media (max-width: 768px) {
  :deep(.el-table) {
    font-size: 12px;
  }

  :deep(.el-table__body-wrapper) {
    overflow-x: auto;
  }
}
</style>

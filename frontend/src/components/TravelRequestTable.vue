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

    <el-table-column prop="status" :label="$t('dashboard.status')" width="140" sortable>
      <template #default="scope">
        <span
          v-if="canChangeStatus(scope.row)"
          :class="['voa-action-tag', scope.row.status]"
          style="cursor: pointer"
          @click="handleStatusClick(scope.row)"
        >
          {{ translateStatus(scope.row.status) }}
        </span>
        <span v-else :class="['voa-action-tag', scope.row.status]">
          {{ translateStatus(scope.row.status) }}
        </span>
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

    <el-table-column :label="$t('users.actions')" width="140" fixed="right">
      <template #default="scope">
        <el-button
          v-if="showDelete && canDelete(scope.row)"
          type="danger"
          :icon="Delete"
          circle
          size="small"
          @click="handleDelete(scope.row)"
        />
        <el-button
          v-if="canCancel(scope.row)"
          type="warning"
          :icon="CircleClose"
          circle
          size="small"
          @click="handleCancel(scope.row)"
        />
        <el-button type="primary" :icon="View" circle size="small" @click="handleView(scope.row)" />
      </template>
    </el-table-column>
  </el-table>

  <!-- Cancel dialog -->
  <el-dialog v-model="cancelDialogVisible" :title="$t('common.confirm')" width="400px" align-center>
    <p>{{ $t('travelRequest.cancelConfirmMessage') }}</p>
    <template #footer>
      <el-button @click="cancelDialogVisible = false">{{ $t('common.cancel') }}</el-button>
      <el-button type="warning" @click="confirmCancel" :loading="cancelling">
        {{ $t('common.confirm') }}
      </el-button>
    </template>
  </el-dialog>

  <!-- Delete dialog -->
  <el-dialog
    v-model="deleteDialogVisible"
    :title="$t('travelRequest.confirmDelete')"
    width="400px"
    align-center
  >
    <p>{{ $t('travelRequest.deleteConfirmMessage') }}</p>
    <template #footer>
      <el-button @click="deleteDialogVisible = false">{{ $t('common.cancel') }}</el-button>
      <el-button type="danger" @click="confirmDelete" :loading="deleting">
        {{ $t('common.delete') }}
      </el-button>
    </template>
  </el-dialog>

  <!-- Quick-status dialog (from tag click) -->
  <el-dialog
    v-model="statusDialogVisible"
    :title="$t('travelRequest.changeStatus')"
    width="400px"
    align-center
  >
    <el-form>
      <el-form-item :label="$t('travelRequest.newStatus')">
        <el-select
          v-model="newStatus"
          :placeholder="$t('travelRequest.selectStatus')"
          style="width: 100%"
        >
          <el-option :label="$t('status.approved')" value="approved" />
          <el-option :label="$t('status.cancelled')" value="cancelled" />
        </el-select>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="statusDialogVisible = false">{{ $t('common.cancel') }}</el-button>
      <el-button type="primary" @click="confirmStatusChange" :loading="changingStatus">
        {{ $t('common.confirm') }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Delete,
  View,
  CircleClose,
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
const emit = defineEmits(['delete', 'status-change', 'cancel', 'view'])

const authStore = useAuthStore()

const deleteDialogVisible = ref(false)
const cancelDialogVisible = ref(false)
const statusDialogVisible = ref(false)
const deleting = ref(false)
const cancelling = ref(false)
const changingStatus = ref(false)
const selectedRequest = ref(null)
const newStatus = ref('')

const tableData = computed(() => props.data)
const showDelete = computed(() => authStore.isAdmin)
const showApprover = computed(() => authStore.isApprover)

const canDelete = (row) => authStore.isAdmin && row.status !== 'approved'

const canCancel = (row) => {
  if (!row || !row.can_be_cancelled) return false
  return true
}

const canChangeStatus = (row) => {
  if (!row) return false
  return authStore.isApprover && row.user_id !== authStore.user?.id
}

const getStatusType = (status) => {
  const types = { requested: 'warning', approved: 'success', cancelled: 'danger' }
  return types[status] || ''
}

const translateStatus = (status) => {
  const translations = {
    requested: t('status.requested'),
    approved: t('status.approved'),
    cancelled: t('status.cancelled'),
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
  const parts = new Date(d + 'T12:00:00')
    .toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
    .split(' ')
  return `${parts[0]} de ${parts[1]} de ${parts[2]}`
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

const handleDelete = (row) => {
  selectedRequest.value = row
  deleteDialogVisible.value = true
}

const handleCancel = (row) => {
  selectedRequest.value = row
  cancelDialogVisible.value = true
}

const confirmCancel = async () => {
  cancelling.value = true
  await emit('cancel', selectedRequest.value.id)
  cancelling.value = false
  cancelDialogVisible.value = false
}

const confirmDelete = async () => {
  deleting.value = true
  await emit('delete', selectedRequest.value.id)
  deleting.value = false
  deleteDialogVisible.value = false
}

const handleStatusClick = (row) => {
  if (!canChangeStatus(row)) return
  selectedRequest.value = row
  newStatus.value = row.status === 'approved' ? 'cancelled' : 'approved'
  statusDialogVisible.value = true
}

const confirmStatusChange = async () => {
  changingStatus.value = true
  await emit('status-change', selectedRequest.value.id, newStatus.value)
  changingStatus.value = false
  statusDialogVisible.value = false
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

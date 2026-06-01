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
import { View, Edit, CircleClose, CircleCheck } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'
import { useDateFormat } from '@/composables/useDateFormat'
import { useTravelType } from '@/composables/useTravelType'
import { useRequestStatus } from '@/composables/useRequestStatus'

const { formatDateShort: formatDate, formatDateLong, formatDateTime } = useDateFormat()
const { travelTypeIcon, getTravelTypeColor, formatRequestId } = useTravelType()
const { getStatusType, translateStatus, isSystemCancellation } = useRequestStatus()
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
  if (!authStore.isApprover) return false
  if (row.user_id === authStore.user?.id) return false
  return row.status === 'requested'
}

const canCancel = (row) => {
  if (!row) return false
  if (row.status === 'expired') return false
  if (!['requested', 'approved'].includes(row.status)) return false
  if (row.can_be_cancelled === false) return false
  if (!authStore.isApprover) return false
  if (row.user_id === authStore.user?.id) return false
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
  if (row.status !== 'requested') return false
  if (!authStore.isApprover) return false
  if (row.user_id === authStore.user?.id) return false
  return true
}

const handleEdit = (row) => {
  emit('edit', row)
}

const canChangeStatus = (row) => {
  if (!row) return false
  if (row.status === 'cancelled' && isSystemCancellation(row.status, row.cancel_reason)) return false
  return authStore.isApprover && row.user_id !== authStore.user?.id
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

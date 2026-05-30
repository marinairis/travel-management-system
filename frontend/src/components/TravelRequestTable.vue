da<template>
  <div class="travel-request-table">
    <div class="table-container">
      <el-table
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        :default-sort="{ prop: 'created_at', order: 'descending' }"
        scroll-x
      >
        <el-table-column prop="id" :label="$t('users.id')" width="80" sortable>
          <template #default="scope">
            <span style="font-family:var(--voa-mono,monospace);font-size:12px;font-weight:700;color:var(--el-color-primary)">
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

        <el-table-column
          prop="departure_date"
          :label="$t('travelRequest.departureDate')"
          min-width="150"
          sortable
        >
          <template #default="scope">
            {{ formatDate(scope.row.departure_date) }}
          </template>
        </el-table-column>

        <el-table-column
          prop="return_date"
          :label="$t('travelRequest.returnDate')"
          min-width="150"
          sortable
        >
          <template #default="scope">
            {{ formatDate(scope.row.return_date) }}
          </template>
        </el-table-column>

        <el-table-column
          prop="travel_type"
          :label="$t('travelRequest.travelType')"
          width="120"
        >
          <template #default="scope">
            <span v-if="scope.row.travel_type">
              {{ $t('travelRequest.travelType_' + scope.row.travel_type) }}
            </span>
            <span v-else class="text-muted">-</span>
          </template>
        </el-table-column>

        <el-table-column prop="status" :label="$t('dashboard.status')" width="140" sortable>
          <template #default="scope">
            <el-tag
              :type="getStatusType(scope.row.status)"
              @click="handleStatusClick(scope.row)"
              :style="canChangeStatus(scope.row) ? 'cursor: pointer;' : ''"
            >
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
            <el-button
              type="primary"
              :icon="View"
              circle
              size="small"
              @click="handleView(scope.row)"
            />
          </template>
        </el-table-column>
      </el-table>

      <!-- Cancel dialog -->
      <el-dialog
        v-model="cancelDialogVisible"
        :title="$t('common.confirm')"
        width="400px"
        align-center
      >
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
            <el-select v-model="newStatus" :placeholder="$t('travelRequest.selectStatus')" style="width: 100%">
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

      <!-- Detail / view dialog -->
      <el-dialog
        v-model="viewDialogVisible"
        :title="$t('travelRequest.requestDetails')"
        :width="isMobile ? '95%' : '620px'"
      >
        <el-descriptions v-if="selectedRequest" :column="1" border>
          <el-descriptions-item :label="$t('users.id')">
            <span style="font-family:var(--voa-mono,monospace);font-size:14px;font-weight:700;color:var(--el-color-primary)">
              {{ formatRequestId(selectedRequest.id) }}
            </span>
          </el-descriptions-item>

          <el-descriptions-item :label="$t('travelRequest.requesterName')">
            {{ selectedRequest.requester_name }}
          </el-descriptions-item>

          <el-descriptions-item :label="$t('travelRequest.user')">
            {{ selectedRequest.user?.name || '-' }}
          </el-descriptions-item>

          <el-descriptions-item :label="$t('travelRequest.destination')">
            {{ selectedRequest.destination }}
          </el-descriptions-item>

          <el-descriptions-item v-if="selectedRequest.travel_type" :label="$t('travelRequest.travelType')">
            {{ $t('travelRequest.travelType_' + selectedRequest.travel_type) }}
          </el-descriptions-item>

          <el-descriptions-item :label="$t('travelRequest.departureDate')">
            {{ formatDate(selectedRequest.departure_date) }}
          </el-descriptions-item>

          <el-descriptions-item :label="$t('travelRequest.returnDate')">
            {{ formatDate(selectedRequest.return_date) }}
          </el-descriptions-item>

          <el-descriptions-item :label="$t('dashboard.status')">
            <template v-if="canChangeStatus(selectedRequest)">
              <el-select v-model="detailStatus" size="small" style="width: 160px">
                <el-option :label="$t('status.requested')" value="requested" :disabled="true" />
                <el-option :label="$t('status.approved')" value="approved" />
                <el-option :label="$t('status.cancelled')" value="cancelled" />
              </el-select>
            </template>
            <el-tag v-else :type="getStatusType(selectedRequest.status)">
              {{ translateStatus(selectedRequest.status) }}
            </el-tag>
          </el-descriptions-item>

          <el-descriptions-item
            v-if="selectedRequest.approved_by"
            :label="$t('travelRequest.approvedBy')"
          >
            {{ selectedRequest.approved_by?.name }}
          </el-descriptions-item>

          <el-descriptions-item v-if="selectedRequest.notes" :label="$t('travelRequest.notes')">
            {{ selectedRequest.notes }}
          </el-descriptions-item>

          <el-descriptions-item :label="$t('travelRequest.createdAt')">
            {{ formatDateTime(selectedRequest.created_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <template #footer>
          <el-button @click="viewDialogVisible = false">{{ $t('common.close') }}</el-button>
          <el-button
            v-if="canChangeStatus(selectedRequest) && detailStatus !== selectedRequest?.status"
            type="primary"
            @click="saveDetailStatus"
            :loading="changingStatus"
          >
            {{ $t('common.save') }}
          </el-button>
        </template>
      </el-dialog>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Delete, View, CircleClose } from '@element-plus/icons-vue'
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
const viewDialogVisible = ref(false)
const deleting = ref(false)
const cancelling = ref(false)
const changingStatus = ref(false)
const selectedRequest = ref(null)
const newStatus = ref('')
const detailStatus = ref('')

const isMobile = computed(() => window.innerWidth <= 768)
const tableData = computed(() => props.data)
const showDelete = computed(() => authStore.isAdmin)
const showApprover = computed(() => authStore.isApprover)

const canDelete = (row) => authStore.isAdmin && row.status !== 'approved'

const canCancel = (row) => {
  if (!row || !row.can_be_cancelled) return false
  // Owner can cancel, approvers use status change instead
  return row.user_id === authStore.user?.id
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

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('pt-BR')
}

// Formatar ID do pedido no formato VG-XXX
const formatRequestId = (id) => {
  if (!id) return '-'
  return `VG-${String(id).padStart(3, '0')}`
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
  selectedRequest.value = row
  detailStatus.value = row.status
  viewDialogVisible.value = true
}

const saveDetailStatus = async () => {
  changingStatus.value = true
  await emit('status-change', selectedRequest.value.id, detailStatus.value)
  changingStatus.value = false
  viewDialogVisible.value = false
}
</script>

<style scoped>
.travel-request-table {
  width: 100%;
}

:deep(.el-table) {
  font-size: 14px;
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

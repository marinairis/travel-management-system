<template>
  <div class="travel-request-table">
    <div class="table-container">
      <el-table
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        :default-sort="{ prop: 'created_at', order: 'descending' }"
        :scroll-x="true"
      >
        <el-table-column prop="id" label="ID" sortable />

        <el-table-column
          prop="requester_name"
          label="Solicitante"
          min-width="200"
          show-overflow-tooltip
        />

        <el-table-column prop="destination" label="Destino" min-width="200" show-overflow-tooltip />

        <el-table-column prop="departure_date" label="Data de Ida" min-width="200" sortable>
          <template #default="scope">
            {{ formatDate(scope.row.departure_date) }}
          </template>
        </el-table-column>

        <el-table-column prop="return_date" label="Data de Volta" min-width="200" sortable>
          <template #default="scope">
            {{ formatDate(scope.row.return_date) }}
          </template>
        </el-table-column>

        <el-table-column prop="status" label="Status" width="140" sortable>
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
          label="Aprovado Por"
          min-width="200"
          show-overflow-tooltip
        >
          <template #default="scope">
            <span v-if="scope.row.approved_by">
              {{ scope.row.approved_by?.name }}
            </span>
            <span v-else class="text-muted">-</span>
          </template>
        </el-table-column>

        <el-table-column label="Ações" width="120" fixed="right">
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
              type="primary"
              :icon="View"
              circle
              size="small"
              @click="handleView(scope.row)"
            />
          </template>
        </el-table-column>
      </el-table>

      <!-- Modal de confirmação de exclusão -->
      <el-dialog v-model="deleteDialogVisible" title="Confirmar Exclusão" width="400" align-center>
        <p>Tem certeza que deseja excluir este pedido de viagem?</p>
        <template #footer>
          <el-button @click="deleteDialogVisible = false">Cancelar</el-button>
          <el-button type="danger" @click="confirmDelete" :loading="deleting"> Excluir </el-button>
        </template>
      </el-dialog>

      <!-- Modal de alteração de status -->
      <el-dialog v-model="statusDialogVisible" title="Alterar Status" width="400" align-center>
        <el-form>
          <el-form-item label="Novo Status">
            <el-select v-model="newStatus" placeholder="Selecione o status" style="width: 100%">
              <el-option label="Aprovado" value="approved" />
              <el-option label="Cancelado" value="cancelled" />
            </el-select>
          </el-form-item>
        </el-form>
        <template #footer>
          <el-button @click="statusDialogVisible = false">Cancelar</el-button>
          <el-button type="primary" @click="confirmStatusChange" :loading="changingStatus">
            Confirmar
          </el-button>
        </template>
      </el-dialog>

      <!-- Modal de detalhes -->
      <el-dialog v-model="viewDialogVisible" title="Detalhes do Pedido" width="600">
        <el-descriptions v-if="selectedRequest" :column="1" border>
          <el-descriptions-item label="ID">
            {{ selectedRequest.id }}
          </el-descriptions-item>
          <el-descriptions-item label="Solicitante">
            {{ selectedRequest.requester_name }}
          </el-descriptions-item>
          <el-descriptions-item label="Usuário">
            {{ selectedRequest.user?.name }}
          </el-descriptions-item>
          <el-descriptions-item label="Destino">
            {{ selectedRequest.destination }}
          </el-descriptions-item>
          <el-descriptions-item label="Data de Ida">
            {{ formatDate(selectedRequest.departure_date) }}
          </el-descriptions-item>
          <el-descriptions-item label="Data de Volta">
            {{ formatDate(selectedRequest.return_date) }}
          </el-descriptions-item>
          <el-descriptions-item label="Status">
            <el-tag :type="getStatusType(selectedRequest.status)">
              {{ translateStatus(selectedRequest.status) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item v-if="selectedRequest.approved_by" label="Aprovado Por">
            {{ selectedRequest.approved_by?.name }}
          </el-descriptions-item>
          <el-descriptions-item v-if="selectedRequest.notes" label="Observações">
            {{ selectedRequest.notes }}
          </el-descriptions-item>
          <el-descriptions-item label="Criado em">
            {{ formatDateTime(selectedRequest.created_at) }}
          </el-descriptions-item>
        </el-descriptions>
      </el-dialog>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Delete, View } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'
import { ElMessageBox } from 'element-plus'

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['delete', 'status-change', 'view'])

const authStore = useAuthStore()

const deleteDialogVisible = ref(false)
const statusDialogVisible = ref(false)
const viewDialogVisible = ref(false)
const deleting = ref(false)
const changingStatus = ref(false)
const selectedRequest = ref(null)
const newStatus = ref('')

const tableData = computed(() => props.data)
const showDelete = computed(() => authStore.isAdmin)
const showApprover = computed(() => authStore.isAdmin)

const canDelete = (row) => {
  return authStore.isAdmin && row.status !== 'approved'
}

const canChangeStatus = (row) => {
  return authStore.isAdmin && row.user_id !== authStore.user?.id
}

const getStatusType = (status) => {
  const types = {
    requested: 'warning',
    approved: 'success',
    cancelled: 'danger',
  }
  return types[status] || ''
}

const translateStatus = (status) => {
  const translations = {
    requested: 'Solicitado',
    approved: 'Aprovado',
    cancelled: 'Cancelado',
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

const handleDelete = (row) => {
  selectedRequest.value = row
  deleteDialogVisible.value = true
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
  viewDialogVisible.value = true
}
</script>

<style scoped>
.text-muted {
  color: var(--el-text-color-secondary);
}

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
}
</style>

<template>
  <div class="logs-container">
    <el-container>
      <el-main class="main-content">
        <div class="page-header">
          <h1 class="page-title">Logs de Atividades</h1>
        </div>

        <!-- Filtros -->
        <el-card class="filter-card" shadow="never">
          <el-form :inline="true" :model="filters">
            <el-form-item label="Ação">
              <el-select
                v-model="filters.action"
                placeholder="Todas"
                clearable
                style="width: 150px"
                @change="handleFilter"
              >
                <el-option label="Criar" value="create" />
                <el-option label="Atualizar" value="update" />
                <el-option label="Deletar" value="delete" />
                <el-option label="Alterar Status" value="status_change" />
                <el-option label="Cancelar" value="cancel" />
              </el-select>
            </el-form-item>

            <el-form-item label="Tipo">
              <el-select
                v-model="filters.model_type"
                placeholder="Todos"
                clearable
                style="width: 200px"
                @change="handleFilter"
              >
                <el-option label="Pedido de Viagem" value="App\Models\TravelRequest" />
                <el-option label="Usuário" value="App\Models\User" />
              </el-select>
            </el-form-item>

            <el-form-item label="Período">
              <el-date-picker
                v-model="dateRange"
                type="daterange"
                range-separator="até"
                start-placeholder="Data início"
                end-placeholder="Data fim"
                format="DD/MM/YYYY"
                value-format="YYYY-MM-DD"
                @change="handleDateChange"
              />
            </el-form-item>

            <el-form-item>
              <el-button :icon="Refresh" @click="handleReset"> Limpar </el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <!-- Tabela -->
        <el-card class="table-card">
          <el-table
            :data="activityLogStore.logs"
            v-loading="activityLogStore.loading"
            style="width: 100%"
          >
            <el-table-column prop="id" label="ID" width="80" />

            <el-table-column prop="user.name" label="Usuário" min-width="150" />

            <el-table-column prop="action" label="Ação" width="140">
              <template #default="scope">
                <el-tag :type="getActionType(scope.row.action)">
                  {{ translateAction(scope.row.action) }}
                </el-tag>
              </template>
            </el-table-column>

            <el-table-column prop="description" label="Descrição" min-width="250" />

            <el-table-column prop="model_type" label="Tipo" width="150">
              <template #default="scope">
                {{ translateModelType(scope.row.model_type) }}
              </template>
            </el-table-column>

            <el-table-column prop="ip_address" label="IP" width="130" />

            <el-table-column prop="created_at" label="Data/Hora" width="180">
              <template #default="scope">
                {{ formatDateTime(scope.row.created_at) }}
              </template>
            </el-table-column>

            <el-table-column label="Detalhes" width="100" fixed="right">
              <template #default="scope">
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

          <!-- Paginação -->
          <div class="pagination-container">
            <el-pagination
              v-model:current-page="currentPage"
              :page-size="activityLogStore.pagination.per_page"
              :total="activityLogStore.pagination.total"
              layout="total, prev, pager, next"
              @current-change="handlePageChange"
            />
          </div>
        </el-card>
      </el-main>
    </el-container>

    <!-- Dialog de detalhes -->
    <el-dialog v-model="showViewDialog" title="Detalhes do Log" width="700">
      <el-descriptions v-if="selectedLog" :column="1" border>
        <el-descriptions-item label="ID">
          {{ selectedLog.id }}
        </el-descriptions-item>
        <el-descriptions-item label="Usuário">
          {{ selectedLog.user?.name || 'Sistema' }}
        </el-descriptions-item>
        <el-descriptions-item label="Ação">
          <el-tag :type="getActionType(selectedLog.action)">
            {{ translateAction(selectedLog.action) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="Descrição">
          {{ selectedLog.description }}
        </el-descriptions-item>
        <el-descriptions-item label="Tipo">
          {{ translateModelType(selectedLog.model_type) }}
        </el-descriptions-item>
        <el-descriptions-item label="ID do Registro">
          {{ selectedLog.model_id || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="IP">
          {{ selectedLog.ip_address }}
        </el-descriptions-item>
        <el-descriptions-item label="User Agent">
          {{ selectedLog.user_agent }}
        </el-descriptions-item>
        <el-descriptions-item label="Data/Hora">
          {{ formatDateTime(selectedLog.created_at) }}
        </el-descriptions-item>
      </el-descriptions>

      <div v-if="selectedLog?.old_values" style="margin-top: 20px">
        <h4>Valores Antigos:</h4>
        <pre>{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
      </div>

      <div v-if="selectedLog?.new_values" style="margin-top: 20px">
        <h4>Valores Novos:</h4>
        <pre>{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useActivityLogStore } from '@/stores/activityLog'
import { View, Refresh } from '@element-plus/icons-vue'

const activityLogStore = useActivityLogStore()

const showViewDialog = ref(false)
const selectedLog = ref(null)
const dateRange = ref([])
const currentPage = ref(1)

const filters = reactive({
  action: '',
  model_type: '',
  start_date: '',
  end_date: '',
  page: 1,
})

onMounted(() => {
  activityLogStore.fetchLogs()
})

const handleFilter = () => {
  filters.page = 1
  currentPage.value = 1
  activityLogStore.fetchLogs(filters)
}

const handleDateChange = (dates) => {
  if (dates) {
    filters.start_date = dates[0]
    filters.end_date = dates[1]
  } else {
    filters.start_date = ''
    filters.end_date = ''
  }
  handleFilter()
}

const handleReset = () => {
  filters.action = ''
  filters.model_type = ''
  filters.start_date = ''
  filters.end_date = ''
  filters.page = 1
  dateRange.value = []
  currentPage.value = 1
  handleFilter()
}

const handlePageChange = (page) => {
  filters.page = page
  activityLogStore.fetchLogs(filters)
}

const handleView = (log) => {
  selectedLog.value = log
  showViewDialog.value = true
}

const getActionType = (action) => {
  const types = {
    create: 'success',
    update: 'warning',
    delete: 'danger',
    status_change: 'info',
    cancel: 'warning',
  }
  return types[action] || ''
}

const translateAction = (action) => {
  const translations = {
    create: 'Criar',
    update: 'Atualizar',
    delete: 'Deletar',
    status_change: 'Alterar Status',
    cancel: 'Cancelar',
  }
  return translations[action] || action
}

const translateModelType = (type) => {
  if (!type) return '-'
  const translations = {
    'App\\Models\\TravelRequest': 'Pedido de Viagem',
    'App\\Models\\User': 'Usuário',
  }
  return translations[type] || type
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('pt-BR')
}
</script>

<style scoped>
.logs-container {
  min-height: calc(100vh - 60px);
  background-color: var(--el-bg-color-page);
  display: flex;
  flex-direction: column;
  padding-top: 60px; /* Espaço para o header fixo */
}

.main-content {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
  flex: 1;
  width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  margin: 0;
}

.filter-card {
  margin-bottom: 24px;
}

.table-card {
  margin-bottom: 24px;
}

.pagination-container {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

pre {
  background-color: var(--el-fill-color-light);
  padding: 12px;
  border-radius: 4px;
  overflow-x: auto;
  font-size: 12px;
}

@media (max-width: 768px) {
  .main-content {
    padding: 16px;
  }

  .page-title {
    font-size: 24px;
  }

  :deep(.el-form--inline .el-form-item) {
    display: block;
    margin-bottom: 12px;
  }
}
</style>

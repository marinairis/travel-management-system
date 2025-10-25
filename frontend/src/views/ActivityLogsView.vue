<template>
  <div class="page-container">
    <el-container>
      <el-main class="main-content">
        <div class="page-header">
          <h1 class="page-title" :class="{ 'dark-theme': themeStore.isDark }">
            {{ $t('activityLogs.title') }}
          </h1>
        </div>

        <el-card class="filter-card" shadow="never">
          <el-form :inline="true" :model="filters">
            <el-form-item :label="$t('activityLogs.action')">
              <el-select
                v-model="filters.action"
                :placeholder="$t('common.all')"
                clearable
                style="width: 150px"
                @change="handleFilter"
              >
                <el-option :label="$t('activityLogs.create')" value="create" />
                <el-option :label="$t('activityLogs.update')" value="update" />
                <el-option :label="$t('activityLogs.delete')" value="delete" />
                <el-option :label="$t('activityLogs.statusChange')" value="status_change" />
                <el-option :label="$t('activityLogs.cancel')" value="cancel" />
              </el-select>
            </el-form-item>

            <el-form-item :label="$t('activityLogs.user')">
              <el-select
                v-model="filters.user_id"
                :placeholder="$t('common.all')"
                clearable
                filterable
                style="width: 200px"
                @change="handleFilter"
              >
                <el-option
                  v-for="user in activityLogStore.users"
                  :key="user.id"
                  :label="user.name"
                  :value="user.id"
                />
              </el-select>
            </el-form-item>

            <el-form-item :label="$t('activityLogs.type')">
              <el-select
                v-model="filters.model_type"
                :placeholder="$t('common.all')"
                clearable
                style="width: 200px"
                @change="handleFilter"
              >
                <el-option
                  :label="$t('activityLogs.travelRequest')"
                  value="App\Models\TravelRequest"
                />
                <el-option :label="$t('activityLogs.userModel')" value="App\Models\User" />
              </el-select>
            </el-form-item>

            <el-form-item>
              <el-button :icon="Refresh" @click="handleReset"> {{ $t('common.clear') }} </el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <el-card class="table-card">
          <el-table
            :data="activityLogStore.logs"
            v-loading="activityLogStore.loading"
            style="width: 100%"
          >
            <el-table-column prop="id" :label="$t('users.id')" width="80" />

            <el-table-column
              prop="user.name"
              :label="$t('activityLogs.user')"
              min-width="200"
              show-overflow-tooltip
            />

            <el-table-column prop="action" :label="$t('activityLogs.action')" width="140">
              <template #default="scope">
                <el-tag
                  :type="getActionType(scope.row.action)"
                  :class="`action-tag action-${scope.row.action}`"
                >
                  {{ translateAction(scope.row.action) }}
                </el-tag>
              </template>
            </el-table-column>

            <el-table-column
              prop="description"
              :label="$t('activityLogs.description')"
              min-width="250"
              show-overflow-tooltip
            />

            <el-table-column
              prop="model_type"
              :label="$t('activityLogs.type')"
              min-width="200"
              show-overflow-tooltip
            >
              <template #default="scope">
                {{ translateModelType(scope.row.model_type) }}
              </template>
            </el-table-column>

            <el-table-column
              prop="ip_address"
              :label="$t('activityLogs.ip')"
              width="130"
              show-overflow-tooltip
            />

            <el-table-column prop="created_at" :label="$t('activityLogs.dateTime')" width="180">
              <template #default="scope">
                {{ formatDateTime(scope.row.created_at) }}
              </template>
            </el-table-column>

            <el-table-column :label="$t('activityLogs.details')" width="100" fixed="right">
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

    <el-dialog v-model="showViewDialog" :title="$t('activityLogs.logDetails')" width="700">
      <el-descriptions v-if="selectedLog" :column="1" border>
        <el-descriptions-item :label="$t('users.id')">
          {{ selectedLog.id }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.user')">
          {{ selectedLog.user?.name || $t('activityLogs.system') }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.action')">
          <el-tag
            :type="getActionType(selectedLog.action)"
            :class="`action-tag action-${selectedLog.action}`"
          >
            {{ translateAction(selectedLog.action) }}
          </el-tag>
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.description')">
          {{ selectedLog.description }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.type')">
          {{ translateModelType(selectedLog.model_type) }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.userId')">
          {{ selectedLog.model_id || '-' }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.ip')">
          {{ selectedLog.ip_address }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.userAgent')">
          {{ selectedLog.user_agent }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.dateTime')">
          {{ formatDateTime(selectedLog.created_at) }}
        </el-descriptions-item>
      </el-descriptions>

      <div v-if="selectedLog?.old_values" style="margin-top: 20px">
        <h4>{{ $t('activityLogs.oldValues') }}:</h4>
        <pre>{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
      </div>

      <div v-if="selectedLog?.new_values" style="margin-top: 20px">
        <h4>{{ $t('activityLogs.newValues') }}:</h4>
        <pre>{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useActivityLogStore } from '@/stores/activityLog'
import { useThemeStore } from '@/stores/theme'
import { View, Refresh } from '@element-plus/icons-vue'

const activityLogStore = useActivityLogStore()
const themeStore = useThemeStore()

const showViewDialog = ref(false)
const selectedLog = ref(null)
const currentPage = ref(1)

const filters = reactive({
  action: '',
  user_id: '',
  model_type: '',
  page: 1,
})

onMounted(() => {
  activityLogStore.fetchLogs()
  activityLogStore.fetchUsers()
})

const handleFilter = () => {
  filters.page = 1
  currentPage.value = 1
  activityLogStore.fetchLogs(filters)
}

const handleReset = () => {
  filters.action = ''
  filters.user_id = ''
  filters.model_type = ''
  filters.page = 1
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
    login: 'primary',
    logout: 'info',
    approve: 'success',
    reject: 'danger',
    view: 'primary',
    export: 'info',
    import: 'warning',
  }
  return types[action] || 'info'
}

const translateAction = (action) => {
  const translations = {
    create: 'Criar',
    update: 'Atualizar',
    delete: 'Deletar',
    status_change: 'Alterar Status',
    cancel: 'Cancelar',
    login: 'Login',
    logout: 'Logout',
    approve: 'Aprovar',
    reject: 'Rejeitar',
    view: 'Visualizar',
    export: 'Exportar',
    import: 'Importar',
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
.action-tag {
  font-weight: 600;
  border-radius: 6px;
  font-size: 12px;
  padding: 4px 8px;
}

.action-create {
  background-color: #f0fdf4 !important;
  color: #166534 !important;
  border-color: #22c55e !important;
}

.action-update {
  background-color: #fef3c7 !important;
  color: #d97706 !important;
  border-color: #f59e0b !important;
}

.action-delete {
  background-color: #fef2f2 !important;
  color: #dc2626 !important;
  border-color: #ef4444 !important;
}

.action-status_change {
  background-color: #f0f9ff !important;
  color: #1d4ed8 !important;
  border-color: #3b82f6 !important;
}

.action-cancel {
  background-color: #fef2f2 !important;
  color: #dc2626 !important;
  border-color: #ef4444 !important;
}

.action-login {
  background-color: #f0fdf4 !important;
  color: #166534 !important;
  border-color: #22c55e !important;
}

.action-logout {
  background-color: #f8fafc !important;
  color: #475569 !important;
  border-color: #94a3b8 !important;
}

.action-approve {
  background-color: #f0fdf4 !important;
  color: #15803d !important;
  border-color: #16a34a !important;
}

.action-reject {
  background-color: #fef2f2 !important;
  color: #b91c1c !important;
  border-color: #dc2626 !important;
}

.action-view {
  background-color: #f0f9ff !important;
  color: #1e40af !important;
  border-color: #2563eb !important;
}

.action-export {
  background-color: #f0f9ff !important;
  color: #7c3aed !important;
  border-color: #8b5cf6 !important;
}

.action-import {
  background-color: #fef3c7 !important;
  color: #a16207 !important;
  border-color: #eab308 !important;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
  color: var(--el-text-color-primary);
  transition: color 0.3s ease;
}

.page-title.dark-theme {
  color: #ffffff;
}
</style>

<template>
  <div>
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('activityLogs.title') }}</h1>
      </div>
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

        <el-form-item v-if="authStore.isAdmin" :label="$t('activityLogs.user')">
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

    <el-card class="table-card voa-logs-card">
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
            <span :class="`voa-action-tag ${scope.row.action}`">
              {{ translateAction(scope.row.action) }}
            </span>
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

    <el-dialog v-model="showViewDialog" :title="$t('activityLogs.logDetails')" width="700">
      <el-descriptions v-if="selectedLog" :column="1" border>
        <el-descriptions-item :label="$t('users.id')">
          {{ selectedLog.id }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.user')">
          {{ selectedLog.user?.name || $t('activityLogs.system') }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.action')">
          <span :class="`voa-action-tag ${selectedLog.action}`">
            {{ translateAction(selectedLog.action) }}
          </span>
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
import { useAuthStore } from '@/stores/auth'
import { View, Refresh } from '@element-plus/icons-vue'

const activityLogStore = useActivityLogStore()
const authStore = useAuthStore()

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
  if (authStore.isAdmin) {
    activityLogStore.fetchUsers()
  }
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
.voa-logs-card :deep(.el-table__body-wrapper) {
  overflow-y: auto;
  max-height: calc(100vh - 340px);
}
</style>

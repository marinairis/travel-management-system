<template>
  <div>
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('activityLogs.title') }}</h1>
        <p class="voa-page-sub">{{ $t('activityLogs.subtitle') }}</p>
      </div>
    </div>

    <el-card class="filter-card" shadow="never" style="margin-bottom: 14px">
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
            <el-option :label="$t('activityLogs.travelRequest')" value="App\Models\TravelRequest" />
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
        <el-table-column prop="id" :label="$t('users.id')" width="90" sortable>
          <template #default="scope">
            <span
              style="
                font-family: var(--voa-mono, monospace);
                font-size: 12px;
                font-weight: 700;
                color: var(--el-color-primary);
              "
            >
              {{ formatLogId(scope.row.id) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column :label="$t('activityLogs.user')" min-width="150">
          <template #default="scope">
            <div style="display: flex; align-items: center; gap: 10px">
              <el-avatar
                :size="32"
                :style="{
                  background: avatarBg(scope.row.user_id),
                  color: '#fff',
                  fontSize: '12px',
                  fontWeight: 700,
                }"
              >
                {{ initials(scope.row.user?.name) }}
              </el-avatar>
              <div>
                <div style="font-weight: 600; font-size: 13.5px">
                  {{ scope.row.user?.name || $t('activityLogs.system') }}
                </div>
                <div style="font-size: 12px; color: var(--el-text-color-secondary)">
                  {{ scope.row.user?.email || '-' }}
                </div>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column prop="action" :label="$t('activityLogs.action')" width="150">
          <template #default="scope">
            <el-tag :type="getActionType(scope.row.action)" size="small">
              {{ translateAction(scope.row.action) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column
          prop="description"
          :label="$t('activityLogs.description')"
          min-width="200"
          show-overflow-tooltip
        />

        <el-table-column
          prop="model_type"
          :label="$t('activityLogs.type')"
          width="150"
          show-overflow-tooltip
        >
          <template #default="scope">
            {{ translateModelType(scope.row.model_type) }}
          </template>
        </el-table-column>

        <el-table-column prop="created_at" :label="$t('activityLogs.dateTime')" width="160">
          <template #default="scope">
            {{ formatDateTime(scope.row.created_at) }}
          </template>
        </el-table-column>

        <el-table-column width="80" fixed="right">
          <template #default="scope">
            <el-tooltip :content="$t('common.view')" placement="top">
              <el-button
                type="primary"
                :icon="View"
                circle
                size="small"
                @click="handleView(scope.row)"
              />
            </el-tooltip>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 20, 50]"
          :total="activityLogStore.pagination.total"
          layout="total, sizes, prev, pager, next"
          @size-change="handlePageChange"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>

    <el-dialog v-model="showViewDialog" :title="$t('activityLogs.logDetails')" width="700">
      <el-descriptions v-if="selectedLog" :column="1" border>
        <el-descriptions-item :label="$t('users.id')">
          <span
            style="
              font-family: var(--voa-mono, monospace);
              font-size: 13px;
              font-weight: 700;
              color: var(--el-color-primary);
            "
          >
            {{ formatLogId(selectedLog.id) }}
          </span>
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.user')">
          {{ selectedLog.user?.name || $t('activityLogs.system') }}
        </el-descriptions-item>

        <el-descriptions-item :label="$t('activityLogs.action')">
          <el-tag :type="getActionType(selectedLog.action)" size="small">
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
import { useAvatar } from '@/composables/useAvatar'
import { useDateFormat } from '@/composables/useDateFormat'

const activityLogStore = useActivityLogStore()
const authStore = useAuthStore()

const { initials, avatarBg } = useAvatar()
const { formatDateTime } = useDateFormat()

const showViewDialog = ref(false)
const selectedLog = ref(null)
const currentPage = ref(1)
const pageSize = ref(10)

const filters = reactive({
  action: '',
  user_id: '',
  model_type: '',
  page: 1,
  per_page: 10,
})

onMounted(() => {
  activityLogStore.fetchLogs(filters)
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
  filters.per_page = pageSize.value
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

const formatLogId = (id) => {
  if (!id) return '-'
  return `LOG-${String(id).padStart(4, '0')}`
}
</script>

<style scoped>
.voa-logs-card :deep(.el-table__body-wrapper) {
  overflow-y: auto;
  max-height: calc(100vh - 340px);
}

.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0 4px;
  border-top: 1px solid var(--el-border-color);
}
</style>

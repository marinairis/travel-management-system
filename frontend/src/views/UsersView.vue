<template>
  <div>
    <!-- Page header -->
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('users.title') }}</h1>
        <p class="voa-page-sub">{{ $t('users.subtitle') }}</p>
      </div>
      <el-button type="primary" @click="openInvite"> + {{ $t('users.inviteUser') }} </el-button>
    </div>

    <!-- Filters Card -->
    <el-card class="filter-card" shadow="never" style="margin-bottom: 14px">
      <el-form :inline="true" :model="filters">
        <el-form-item :label="$t('users.userType')">
          <el-select
            v-model="filters.userType"
            :placeholder="$t('common.all')"
            clearable
            style="width: 160px"
            @change="handleFilter"
          >
            <el-option :label="$t('users.roleAdmin')" value="admin" />
            <el-option :label="$t('users.roleManager')" value="manager" />
            <el-option :label="$t('users.roleRequester')" value="requester" />
          </el-select>
        </el-form-item>

        <el-form-item :label="$t('users.email')">
          <el-input
            v-model="filters.email"
            :placeholder="$t('users.emailPlaceholder')"
            clearable
            style="width: 250px"
            @input="handleEmailInput"
          />
        </el-form-item>

        <el-form-item>
          <el-button :icon="Refresh" @click="handleReset"> {{ $t('common.clear') }} </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Table Card -->
    <el-card shadow="never" class="voa-users-card">
      <el-table :data="userStore.users" v-loading="userStore.loading" style="width: 100%">
        <!-- Avatar + name column -->
        <el-table-column :label="$t('users.name')" min-width="200">
          <template #default="scope">
            <div style="display: flex; align-items: center; gap: 10px">
              <el-avatar
                :size="32"
                :style="{
                  background: avatarBg(scope.row.id),
                  color: '#fff',
                  fontSize: '12px',
                  fontWeight: 700,
                }"
              >
                {{ initials(scope.row.name) }}
              </el-avatar>
              <div>
                <div style="font-weight: 600; font-size: 13.5px">{{ scope.row.name }}</div>
                <div style="font-size: 12px; color: var(--el-text-color-secondary)">
                  {{ scope.row.email }}
                </div>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column prop="role" :label="$t('users.type')" width="130">
          <template #default="scope">
            <el-tag :type="getRoleTagType(scope.row.role)" size="small">
              {{ getRoleLabel(scope.row.role) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column
          prop="travel_requests_count"
          :label="$t('users.requests')"
          width="100"
          align="center"
        >
          <template #default="scope">
            <span style="font-family: var(--voa-mono, monospace)">{{
              scope.row.travel_requests_count ?? 0
            }}</span>
          </template>
        </el-table-column>

        <el-table-column :label="$t('users.status')" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.is_active ? 'success' : 'danger'" size="small">
              {{ scope.row.is_active ? $t('users.active') : $t('users.inactive') }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column :label="$t('users.actions')" width="180" fixed="right">
          <template #default="scope">
            <el-button
              type="primary"
              :icon="Edit"
              circle
              size="small"
              @click="handleEdit(scope.row)"
            />
            <el-tooltip
              :content="scope.row.is_active ? $t('users.deactivate') : $t('users.activate')"
              placement="top"
            >
              <el-button
                v-if="scope.row.id !== authStore.user?.id"
                :type="scope.row.is_active ? 'warning' : 'success'"
                circle
                size="small"
                @click="handleToggleStatus(scope.row)"
              >
                <span>{{ scope.row.is_active ? '⛔' : '✅' }}</span>
              </el-button>
            </el-tooltip>
            <el-button
              v-if="scope.row.id !== authStore.user?.id"
              type="danger"
              :icon="Delete"
              circle
              size="small"
              @click="handleDelete(scope.row)"
            />
          </template>
        </el-table-column>
      </el-table>

      <!-- Pagination -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 20, 50]"
          :total="userStore.pagination.total"
          layout="total, sizes, prev, pager, next"
          @size-change="handlePageChange"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>

    <!-- Edit dialog -->
    <el-dialog v-model="showEditDialog" :title="$t('users.editUser')" width="500px">
      <el-form ref="formRef" :model="editForm" :rules="rules" label-position="top">
        <el-form-item :label="$t('users.name')" prop="name">
          <el-input v-model="editForm.name" />
        </el-form-item>
        <el-form-item :label="$t('users.userType')" prop="role">
          <el-select v-model="editForm.role" style="width: 100%">
            <el-option value="requester" :label="$t('users.roleRequester')" />
            <el-option value="manager" :label="$t('users.roleManager')" />
            <el-option value="admin" :label="$t('users.roleAdmin')" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showEditDialog = false">{{ $t('common.cancel') }}</el-button>
        <el-button type="primary" @click="handleUpdate" :loading="updating">{{
          $t('common.save')
        }}</el-button>
      </template>
    </el-dialog>

    <!-- Delete dialog -->
    <el-dialog
      v-model="showDeleteDialog"
      :title="$t('users.confirmDelete')"
      width="400px"
      align-center
    >
      <p>{{ $t('users.deleteUserConfirm') }}</p>
      <template #footer>
        <el-button @click="showDeleteDialog = false">{{ $t('common.cancel') }}</el-button>
        <el-button type="danger" @click="confirmDelete" :loading="deleting">{{
          $t('common.delete')
        }}</el-button>
      </template>
    </el-dialog>

    <!-- Invite dialog -->
    <el-dialog
      v-model="showInviteDialog"
      :title="$t('users.inviteUser')"
      width="480px"
      destroy-on-close
      @closed="resetInviteForm"
    >
      <!-- Success state -->
      <div v-if="inviteSent" class="voa-sent-center">
        <div class="voa-sent-icon">✓</div>
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 6px">
          {{ $t('users.inviteSentTitle') }}
        </div>
        <div style="color: var(--el-text-color-secondary); font-size: 14px">
          {{ $t('users.inviteSentMsg') }}
        </div>
        <div style="font-weight: 700; margin-top: 4px">{{ inviteForm.email }}</div>
        <el-button style="margin-top: 18px" @click="resetInviteForm">{{
          $t('users.inviteAnother')
        }}</el-button>
      </div>

      <!-- Invite form -->
      <div v-else>
        <p style="color: var(--el-text-color-secondary); font-size: 13.5px; margin: -8px 0 16px">
          {{ $t('users.inviteSubtitle') }}
        </p>
        <div style="display: flex; flex-direction: column; gap: 13px">
          <div>
            <div
              style="
                font-size: 13px;
                font-weight: 500;
                margin-bottom: 5px;
                color: var(--el-text-color-regular);
              "
            >
              {{ $t('users.inviteEmail') }} <span style="color: var(--el-color-danger)">*</span>
            </div>
            <el-input
              v-model="inviteForm.email"
              type="email"
              :placeholder="$t('users.inviteEmailPh')"
            />
            <div
              v-if="inviteErrors.email"
              style="font-size: 12px; color: var(--el-color-danger); margin-top: 4px"
            >
              {{ inviteErrors.email }}
            </div>
          </div>
          <div>
            <div
              style="
                font-size: 13px;
                font-weight: 500;
                margin-bottom: 5px;
                color: var(--el-text-color-regular);
              "
            >
              {{ $t('users.inviteRoleSelect') }}
            </div>
            <el-select v-model="inviteForm.role" style="width: 100%">
              <el-option value="requester" :label="$t('users.roleRequester')" />
              <el-option value="manager" :label="$t('users.roleManager')" />
              <el-option value="admin" :label="$t('users.roleAdmin')" />
            </el-select>
          </div>
          <!-- Role description card -->
          <div class="voa-role-hint">
            <strong>{{ getRoleLabel(inviteForm.role) }}:</strong>
            {{ roleDesc(inviteForm.role) }}
          </div>
        </div>
      </div>

      <template v-if="!inviteSent" #footer>
        <el-button @click="showInviteDialog = false">{{ $t('common.cancel') }}</el-button>
        <el-button type="primary" @click="handleInvite" :loading="inviting">
          {{ inviting ? $t('users.inviting') : $t('users.inviteSend') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Edit, Delete, Refresh } from '@element-plus/icons-vue'

const { t } = useI18n()
const userStore = useUserStore()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const showInviteDialog = ref(false)
const inviteSent = ref(false)
const updating = ref(false)
const deleting = ref(false)
const inviting = ref(false)
const selectedUser = ref(null)
const formRef = ref(null)
const inviteErrors = ref({})

const currentPage = ref(1)
const pageSize = ref(10)

const filters = reactive({
  userType: '',
  email: '',
})

const editForm = reactive({ name: '', role: 'requester' })
const inviteForm = reactive({ email: '', role: 'requester' })

const rules = {
  name: [{ required: true, message: t('users.nameRequired'), trigger: 'blur' }],
  role: [{ required: true, message: t('users.roleRequired'), trigger: 'change' }],
}

function initials(name) {
  const parts = String(name || '')
    .trim()
    .split(' ')
  return ((parts[0]?.[0] || '') + (parts[parts.length - 1]?.[0] || '')).toUpperCase()
}

function avatarBg(id) {
  const c = [
    'var(--avatar-color-1)',
    'var(--avatar-color-2)',
    'var(--avatar-color-3)',
    'var(--avatar-color-4)',
    'var(--avatar-color-5)',
    'var(--avatar-color-6)',
  ]
  let h = 0
  const s = String(id || '')
  for (let i = 0; i < s.length; i++) h = s.charCodeAt(i) + ((h << 5) - h)
  return c[Math.abs(h) % c.length]
}

const getRoleTagType = (role) => ({ admin: 'danger', manager: 'warning' })[role] || 'info'
const getRoleLabel = (role) =>
  ({ admin: t('users.roleAdmin'), manager: t('users.roleManager') })[role] ||
  t('users.roleRequester')
const roleDesc = (role) =>
  ({
    requester: t('users.roleDescRequester'),
    manager: t('users.roleDescManager'),
    admin: t('users.roleDescAdmin'),
  })[role] || ''

const updateQueryParams = () => {
  const query = {}
  if (filters.userType) query.user_type = filters.userType
  if (filters.email) query.email = filters.email
  if (currentPage.value > 1) query.page = currentPage.value
  if (pageSize.value !== 10) query.per_page = pageSize.value
  
  router.replace({ query })
}

const loadFiltersFromQuery = () => {
  filters.userType = route.query.user_type || ''
  filters.email = route.query.email || ''
  currentPage.value = parseInt(route.query.page) || 1
  pageSize.value = parseInt(route.query.per_page) || 10
}

const handleFilter = () => {
  updateQueryParams()
  userStore.fetchUsers({
    userType: filters.userType,
    email: filters.email,
    page: currentPage.value,
    per_page: pageSize.value,
  })
}

let emailTimeout = null
const handleEmailInput = () => {
  if (emailTimeout) clearTimeout(emailTimeout)
  emailTimeout = setTimeout(handleFilter, 500)
}

const handleReset = () => {
  filters.userType = ''
  filters.email = ''
  currentPage.value = 1
  router.replace({ query: {} })
  handleFilter()
}

const handlePageChange = () => {
  updateQueryParams()
  userStore.fetchUsers({
    userType: filters.userType,
    email: filters.email,
    page: currentPage.value,
    per_page: pageSize.value,
  })
}

const openInvite = () => {
  resetInviteForm()
  showInviteDialog.value = true
}

const resetInviteForm = () => {
  inviteForm.email = ''
  inviteForm.role = 'requester'
  inviteErrors.value = {}
  inviteSent.value = false
}

onMounted(() => {
  loadFiltersFromQuery()
  userStore.fetchUsers({
    userType: filters.userType,
    email: filters.email,
    page: currentPage.value,
    per_page: pageSize.value,
  })
})

const handleEdit = (user) => {
  selectedUser.value = user
  editForm.name = user.name
  editForm.role = user.role || 'requester'
  showEditDialog.value = true
}

const handleUpdate = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (valid) {
      updating.value = true
      const success = await userStore.updateUser(selectedUser.value.id, editForm)
      updating.value = false
      if (success) {
        showEditDialog.value = false
        if (selectedUser.value.id === authStore.user?.id) {
          await authStore.fetchUser()
          router.go(0)
        }
      }
    }
  })
}

const handleDelete = (user) => {
  selectedUser.value = user
  showDeleteDialog.value = true
}
const confirmDelete = async () => {
  deleting.value = true
  await userStore.deleteUser(selectedUser.value.id)
  deleting.value = false
  showDeleteDialog.value = false
}

const handleInvite = async () => {
  inviteErrors.value = {}
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(inviteForm.email)) {
    inviteErrors.value.email = t('auth.emailInvalid')
    return
  }
  inviting.value = true
  const success = await userStore.inviteUser(inviteForm)
  inviting.value = false
  if (success) inviteSent.value = true
}

const handleToggleStatus = async (user) => {
  await userStore.toggleUserStatus(user.id)
}
</script>

<style scoped>
.voa-users-card :deep(.el-table__body-wrapper) {
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
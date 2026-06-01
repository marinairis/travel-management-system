<template>
  <div>
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('users.title') }}</h1>
        <p class="voa-page-sub">{{ $t('users.subtitle') }}</p>
      </div>
      <el-button type="primary" @click="openInvite"> + {{ $t('users.inviteUser') }} </el-button>
    </div>

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

        <el-form-item :label="$t('users.status')">
          <el-select
            v-model="filters.status"
            :placeholder="$t('common.all')"
            clearable
            style="width: 140px"
            @change="handleFilter"
          >
            <el-option :label="$t('users.active')" value="active" />
            <el-option :label="$t('users.inactive')" value="inactive" />
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

    <el-card shadow="never" class="voa-data-card">
      <el-table :data="tableRows" v-loading="userStore.isLoading" style="width: 100%">
        <el-table-column :label="$t('users.name')" min-width="150">
          <template #default="scope">
            <div v-if="scope.row.is_invited" style="display: flex; align-items: center; gap: 10px">
              <el-avatar :size="32" style="background: #d0d0d0; color: #888; font-size: 12px; font-weight: 700;">
                ?
              </el-avatar>
              <div>
                <div style="font-size: 12px; color: var(--el-text-color-placeholder); font-style: italic">
                  {{ $t('users.pendingRegistration') }}
                </div>
                <div style="font-size: 12px; color: var(--el-text-color-secondary)">
                  {{ scope.row.email }}
                </div>
              </div>
            </div>
            <div v-else style="display: flex; align-items: center; gap: 10px">
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

        <el-table-column prop="role" :label="$t('users.profile')" width="150">
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
              scope.row.is_invited ? '—' : (scope.row.travel_requests_count ?? 0)
            }}</span>
          </template>
        </el-table-column>

        <el-table-column :label="$t('users.status')" width="140">
          <template #default="scope">
            <el-tag v-if="scope.row.is_invited" :type="scope.row.is_expired ? 'info' : 'warning'" size="small">
              {{ scope.row.is_expired ? $t('users.inviteExpired') : $t('users.invited') }}
            </el-tag>
            <el-tag v-else :type="scope.row.is_active ? 'success' : 'danger'" size="small">
              {{ scope.row.is_active ? $t('users.active') : $t('users.inactive') }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column width="170">
          <template #default="scope">
            <el-tooltip
              v-if="!scope.row.is_invited && scope.row.id !== authStore.user?.id"
              :content="scope.row.is_active ? $t('users.deactivate') : $t('users.activate')"
              placement="top"
            >
              <el-tag
                :type="scope.row.is_active ? 'warning' : 'success'"
                class="clickable-tag"
                @click="handleStatusClick(scope.row)"
              >
                {{ scope.row.is_active ? $t('users.deactivate') : $t('users.activate') }}
              </el-tag>
            </el-tooltip>
          </template>
        </el-table-column>

        <el-table-column :label="$t('users.actions')" width="80" fixed="right">
          <template #default="scope">
            <el-tooltip v-if="scope.row.is_invited" :content="$t('users.resendInvite')" placement="top">
              <el-button
                type="warning"
                :icon="Message"
                circle
                size="small"
                :loading="resendingId === scope.row.id"
                @click="handleResend(scope.row)"
              />
            </el-tooltip>
            <el-tooltip v-else :content="$t('common.edit')" placement="top">
              <el-button
                type="primary"
                :icon="Edit"
                circle
                size="small"
                @click="handleEdit(scope.row)"
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
          :total="userStore.pagination.total"
          layout="total, sizes, prev, pager, next"
          @size-change="handlePageChange"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>

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

    <el-dialog
      v-model="showInviteDialog"
      :title="$t('users.inviteUser')"
      width="480px"
      destroy-on-close
      @closed="resetInviteForm"
    >
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
import { useAuthStore } from '@/stores/auth'
import { useUserStore } from '@/stores/user'
import { Delete, Edit, Message, Refresh } from '@element-plus/icons-vue'
import { ElMessageBox } from 'element-plus'
import { computed, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useAvatar } from '@/composables/useAvatar'
import { useRole } from '@/composables/useRole'
import { useListPage } from '@/composables/useListPage'

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
const resendingId = ref(null)
const selectedUser = ref(null)
const formRef = ref(null)
const inviteErrors = ref({})

const tableRows = computed(() => [
  ...userStore.pendingInvitations.map((inv) => ({ ...inv, is_invited: true })),
  ...userStore.users,
])

const { currentPage, pageSize, paginationParams, resetPage } = useListPage(route)

const filters = reactive({
  userType: '',
  status: '',
  email: '',
})

const editForm = reactive({ name: '', role: 'requester' })
const inviteForm = reactive({ email: '', role: 'requester' })

const rules = {
  name: [{ required: true, message: t('users.nameRequired'), trigger: 'blur' }],
  role: [{ required: true, message: t('users.roleRequired'), trigger: 'change' }],
}

const { initials, avatarBg } = useAvatar()
const { getRoleLabel, getRoleTagType } = useRole()
const roleDesc = (role) =>
  ({
    requester: t('users.roleDescRequester'),
    manager: t('users.roleDescManager'),
    admin: t('users.roleDescAdmin'),
  })[role] || ''

const updateQueryParams = () => {
  const query = {}
  if (filters.userType) query.user_type = filters.userType
  if (filters.status) query.status = filters.status
  if (filters.email) query.email = filters.email
  if (currentPage.value > 1) query.page = currentPage.value
  if (pageSize.value !== 10) query.per_page = pageSize.value

  router.replace({ query })
}

const loadFiltersFromQuery = () => {
  filters.userType = route.query.user_type || ''
  filters.status = route.query.status || ''
  filters.email = route.query.email || ''
}

const fetchUsers = () => userStore.fetchUsers({ ...filters, ...paginationParams() })

const handleFilter = () => {
  updateQueryParams()
  fetchUsers()
}

let emailTimeout = null
const handleEmailInput = () => {
  if (emailTimeout) clearTimeout(emailTimeout)
  emailTimeout = setTimeout(handleFilter, 500)
}

const handleReset = () => {
  filters.userType = ''
  filters.status = ''
  filters.email = ''
  resetPage()
  router.replace({ query: {} })
  fetchUsers()
}

const handlePageChange = () => {
  updateQueryParams()
  fetchUsers()
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
  fetchUsers()
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

const handleDelete = async (user) => {
  selectedUser.value = user
  const count = await userStore.getPendingRequestsCount(user.id)
  selectedUser.value.pendingRequestsCount = count

  if (count > 0) {
    ElMessageBox.confirm(
      t('users.confirmDeleteMessage', { name: user.name, count: count }),
      t('users.confirmDeleteTitle'),
      {
        confirmButtonText: t('common.confirm'),
        cancelButtonText: t('common.cancel'),
        type: 'warning',
      }
    ).then(async () => {
      await confirmDelete()
    }).catch(() => {})
  } else {
    showDeleteDialog.value = true
  }
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

const handleResend = async (invitation) => {
  try {
    await ElMessageBox.confirm(
      t('users.confirmResendMessage', { email: invitation.email }),
      t('users.confirmResendTitle'),
      {
        confirmButtonText: t('common.confirm'),
        cancelButtonText: t('common.cancel'),
        type: 'info',
      }
    )
    resendingId.value = invitation.id
    await userStore.resendInvitation(invitation.id)
    resendingId.value = null
  } catch {
    resendingId.value = null
  }
}

const handleStatusClick = async (user) => {
  const action = user.is_active ? 'deactivate' : 'activate'
  const title = user.is_active ? t('users.confirmDeactivateTitle') : t('users.confirmActivateTitle')
  
  let message
  if (user.is_active) {
    const count = await userStore.getPendingRequestsCount(user.id)
    message = count > 0
      ? t('users.confirmDeactivateMessage', { name: user.name, count: count })
      : t('users.confirmDeactivateSimple', { name: user.name })
  } else {
    message = t('users.confirmActivateMessage', { name: user.name })
  }

  try {
    await ElMessageBox.confirm(message, title, {
      confirmButtonText: t('common.confirm'),
      cancelButtonText: t('common.cancel'),
      type: user.is_active ? 'warning' : 'info',
    })
    await userStore.toggleUserStatus(user.id)
  } catch {
  }
}
</script>

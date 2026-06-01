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

    <UserEditDialog
      v-model="showEditDialog"
      :initial-name="selectedUser?.name || ''"
      :initial-role="selectedUser?.role || 'requester'"
      :is-loading="updating"
      @submit="handleUpdate"
    />

    <UserDeleteDialog v-model="showDeleteDialog" :is-loading="deleting" @confirm="confirmDelete" />

    <UserInviteDialog v-model="showInviteDialog" />
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useUserStore } from '@/stores/user'
import { Edit, Message, Refresh } from '@element-plus/icons-vue'
import { ElMessageBox } from 'element-plus'
import { computed, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useAvatar } from '@/composables/useAvatar'
import { useRole } from '@/composables/useRole'
import { useListPage } from '@/composables/useListPage'
import UserInviteDialog from '@/components/UserInviteDialog.vue'
import UserEditDialog from '@/components/UserEditDialog.vue'
import UserDeleteDialog from '@/components/UserDeleteDialog.vue'

const { t } = useI18n()
const userStore = useUserStore()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const showInviteDialog = ref(false)
const updating = ref(false)
const deleting = ref(false)
const resendingId = ref(null)
const selectedUser = ref(null)

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

const { initials, avatarBg } = useAvatar()
const { getRoleLabel, getRoleTagType } = useRole()

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
  showInviteDialog.value = true
}

onMounted(() => {
  loadFiltersFromQuery()
  fetchUsers()
})

const handleEdit = (user) => {
  selectedUser.value = user
  showEditDialog.value = true
}

const handleUpdate = async (formData) => {
  updating.value = true
  const success = await userStore.updateUser(selectedUser.value.id, formData)
  updating.value = false
  if (success) {
    showEditDialog.value = false
    if (selectedUser.value.id === authStore.user?.id) {
      await authStore.fetchUser()
      router.go(0)
    }
  }
}

const confirmDelete = async () => {
  deleting.value = true
  await userStore.deleteUser(selectedUser.value.id)
  deleting.value = false
  showDeleteDialog.value = false
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
    // user cancelled the confirmation dialog
  }
}
</script>

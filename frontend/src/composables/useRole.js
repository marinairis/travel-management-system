import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

export function useRole() {
  const { t } = useI18n()
  const authStore = useAuthStore()

  const currentUserRoleLabel = computed(() => {
    if (authStore.isAdmin) return t('users.roleAdmin')
    if (authStore.isManager) return t('users.roleManager')
    return t('users.roleRequester')
  })

  const currentUserRoleTagType = computed(() => {
    if (authStore.isAdmin) return 'danger'
    if (authStore.isManager) return 'warning'
    return 'info'
  })

  const getRoleLabel = (role) =>
    ({ admin: t('users.roleAdmin'), manager: t('users.roleManager') })[role] ||
    t('users.roleRequester')

  const getRoleTagType = (role) => ({ admin: 'danger', manager: 'warning' })[role] || 'info'

  return { currentUserRoleLabel, currentUserRoleTagType, getRoleLabel, getRoleTagType }
}

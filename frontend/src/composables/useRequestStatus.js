import { useI18n } from 'vue-i18n'

const SYSTEM_CANCEL_PATTERNS = ['Usuário desativado', 'Usuário excluído']

export function useRequestStatus() {
  const { t } = useI18n()

  const getStatusType = (status) =>
    ({ requested: 'warning', approved: 'success', cancelled: 'danger', expired: 'info' })[status] ||
    ''

  const translateStatus = (status) =>
    ({
      requested: t('status.requested'),
      approved: t('status.approved'),
      cancelled: t('status.cancelled'),
      expired: t('status.expired'),
    })[status] || status

  const isSystemCancellation = (status, cancelReason) => {
    if (status !== 'cancelled') return false
    return SYSTEM_CANCEL_PATTERNS.some((pattern) => cancelReason?.includes(pattern))
  }

  return { getStatusType, translateStatus, isSystemCancellation }
}

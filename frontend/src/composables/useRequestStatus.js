import { useI18n } from 'vue-i18n'

export function useRequestStatus() {
  const { t } = useI18n()

  const getStatusType = (status) =>
    ({ requested: 'warning', approved: 'success', cancelled: 'danger', expired: 'info' }[status] || '')

  const translateStatus = (status) =>
    ({
      requested: t('status.requested'),
      approved: t('status.approved'),
      cancelled: t('status.cancelled'),
      expired: t('status.expired'),
    })[status] || status

  const isSystemCancellation = (status, cancelReason) => {
    if (status !== 'cancelled') return false
    const systemPatterns = ['usuário desativado', 'usuário excluído', 'Usuário desativado', 'Usuário excluído']
    return systemPatterns.some((pattern) => cancelReason?.includes(pattern))
  }

  return { getStatusType, translateStatus, isSystemCancellation }
}

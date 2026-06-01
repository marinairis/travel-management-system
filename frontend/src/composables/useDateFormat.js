import { useI18n } from 'vue-i18n'

export function useDateFormat() {
  const { locale } = useI18n()

  const formatDate = (date) => {
    if (!date) return '-'
    const datePart = typeof date === 'string' && date.includes('T') ? date.split('T')[0] : date
    return new Date(datePart + 'T12:00:00').toLocaleDateString(locale.value, {
      day: '2-digit',
      month: 'short',
    })
  }

  const formatDateWithYear = (date) => {
    if (!date) return '-'
    const datePart = typeof date === 'string' && date.includes('T') ? date.split('T')[0] : date
    return new Date(datePart + 'T12:00:00').toLocaleDateString(locale.value, {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    })
  }

  const formatDateShort = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString(locale.value)
  }

  const formatDateLong = (date) => {
    if (!date) return '-'
    const datePart = typeof date === 'string' && date.includes('T') ? date.split('T')[0] : date
    const dateObj = new Date(datePart + 'T12:00:00')
    const day = dateObj.getDate().toString().padStart(2, '0')
    const month = dateObj.toLocaleDateString(locale.value, { month: 'short' })
    return `${day} de ${month}`
  }

  const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString(locale.value)
  }

  const formatDateTimeCompact = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString(locale.value, {
      day: '2-digit',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  return {
    formatDate,
    formatDateWithYear,
    formatDateShort,
    formatDateLong,
    formatDateTime,
    formatDateTimeCompact,
  }
}

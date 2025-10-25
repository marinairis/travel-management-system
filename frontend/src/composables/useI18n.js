import { useI18n as useVueI18n } from 'vue-i18n'
import { useLocaleStore } from '@/stores/locale'

export function useI18n() {
  const { t, locale } = useVueI18n()
  const localeStore = useLocaleStore()

  const changeLocale = (newLocale) => {
    locale.value = newLocale
    localeStore.setLocale(newLocale)
  }

  const initLocale = () => {
    const savedLocale = localStorage.getItem('locale')
    if (savedLocale && ['pt-BR', 'en-US'].includes(savedLocale)) {
      locale.value = savedLocale
      localeStore.setLocale(savedLocale)
    }
  }

  return {
    t,
    locale,
    changeLocale,
    initLocale,
  }
}

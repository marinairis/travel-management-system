import { createI18n } from 'vue-i18n'
import ptBR from './locales/pt-BR.json'
import en from './locales/en.json'
import es from './locales/es.json'

const messages = {
  'pt-BR': ptBR,
  en: en,
  es: es,
}

const savedLocale = (() => {
  try {
    const raw = localStorage.getItem('locale-currentLocale')
    if (raw && ['pt-BR', 'en', 'es'].includes(raw)) return raw
  } catch {
    // localStorage unavailable
  }
  const lang = navigator.language || 'pt-BR'
  if (lang.startsWith('pt')) return 'pt-BR'
  if (lang.startsWith('es')) return 'es'
  return 'en'
})()

const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'pt-BR',
  messages,
})

export { i18n }
export default i18n

import { createI18n } from 'vue-i18n'
import ptBR from './locales/pt-BR.json'
import enUS from './locales/en-US.json'

const messages = {
  'pt-BR': ptBR,
  'en-US': enUS,
}

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR', // idioma padrão
  fallbackLocale: 'pt-BR',
  messages,
})

export default i18n

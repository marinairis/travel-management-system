import { defineStore } from 'pinia'
import ptBr from 'element-plus/dist/locale/pt-br.mjs'
import en from 'element-plus/dist/locale/en.mjs'
import es from 'element-plus/dist/locale/es.mjs'

export const useLocaleStore = defineStore('locale', {
  state: () => ({
    currentLocale: detectBrowserLocale(),
    elementPlusLocales: {
      'pt-BR': ptBr,
      'en': en,
      'es': es,
    },
  }),

  getters: {
    currentElementPlusLocale: (state) => {
      return state.elementPlusLocales[state.currentLocale] || ptBr
    },
  },

  actions: {
    setLocale(locale) {
      this.currentLocale = locale
    },
  },

  persist: {
    storage: localStorage,
    paths: ['currentLocale'],
  },
})

function detectBrowserLocale() {
  const saved = localStorage.getItem('locale-currentLocale')
  if (saved) return saved
  const lang = navigator.language || 'pt-BR'
  if (lang.startsWith('pt')) return 'pt-BR'
  if (lang.startsWith('es')) return 'es'
  return 'en'
}

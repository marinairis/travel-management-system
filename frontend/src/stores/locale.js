import { defineStore } from 'pinia'
import ptBr from 'element-plus/dist/locale/pt-br.mjs'
import en from 'element-plus/dist/locale/en.mjs'

export const useLocaleStore = defineStore('locale', {
  state: () => ({
    currentLocale: 'pt-BR',
    elementPlusLocales: {
      'pt-BR': ptBr,
      'en-US': en,
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
      localStorage.setItem('locale', locale)
    },

    initLocale() {
      const savedLocale = localStorage.getItem('locale')
      if (savedLocale && ['pt-BR', 'en-US'].includes(savedLocale)) {
        this.currentLocale = savedLocale
      }
    },
  },
})

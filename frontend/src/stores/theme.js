import { defineStore } from 'pinia'

export const useThemeStore = defineStore('theme', {
  state: () => ({
    isDark: false,
  }),

  actions: {
    toggleTheme() {
      this.isDark = !this.isDark
      this.applyTheme()
    },

    applyTheme() {
      if (this.isDark) {
        document.documentElement.classList.add('dark')
      } else {
        document.documentElement.classList.remove('dark')
      }
    },

    initTheme() {
      this.applyTheme()
    },
  },

  persist: {
    storage: localStorage,
    paths: ['isDark'],
  },
})

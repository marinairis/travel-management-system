import { defineStore } from 'pinia'
import api from '@/plugins/axios'
import router from '@/router'
import { ElMessage } from 'element-plus'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.is_admin || false,
  },

  actions: {
    async login(credentials) {
      try {
        const response = await api.post('/login', credentials)
        if (response.data.success) {
          this.token = response.data.data.token
          this.user = response.data.data.user
          ElMessage.success('Login realizado com sucesso!')
          router.push('/')
        }
      } catch (error) {
        throw error
      }
    },

    async register(userData) {
      try {
        const response = await api.post('/register', userData)
        if (response.data.success) {
          this.token = response.data.data.token
          this.user = response.data.data.user
          ElMessage.success('Cadastro realizado com sucesso!')
          router.push('/')
        }
      } catch (error) {
        throw error
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Erro ao fazer logout:', error)
      } finally {
        this.user = null
        this.token = null
        router.push('/login')
        ElMessage.success('Logout realizado com sucesso!')
      }
    },

    async fetchUser() {
      try {
        const response = await api.get('/me')
        if (response.data.success) {
          this.user = response.data.data
        }
      } catch (error) {
        this.logout()
      }
    },

    async forgotPassword(email) {
      try {
        const response = await api.post('/forgot-password', { email })
        if (response.data.success) {
          ElMessage.success(response.data.message)
        }
      } catch (error) {
        throw error
      }
    },

    async resetPassword(data) {
      try {
        const response = await api.post('/reset-password', data)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          router.push('/login')
        }
      } catch (error) {
        throw error
      }
    },
  },

  persist: {
    storage: localStorage,
    paths: ['user', 'token'],
  },
})

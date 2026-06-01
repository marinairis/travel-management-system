import { defineStore } from 'pinia'
import * as authRepository from '@/plugins/authRepository'
import router from '@/router'
import { ElMessage } from 'element-plus'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isManager: (state) => state.user?.role === 'manager',
    isRequester: (state) => state.user?.role === 'requester',
    isApprover: (state) => ['admin', 'manager'].includes(state.user?.role),
  },

  actions: {
    async login(credentials) {
      const response = await authRepository.login(credentials)
      if (response.data.success) {
        this.token = response.data.data.token
        this.user = response.data.data.user
        ElMessage.success(response.data.message)
        router.push('/')
      }
    },

    async register(userData) {
      const response = await authRepository.register(userData)
      if (response.data.success) {
        this.token = response.data.data.token
        this.user = response.data.data.user
        ElMessage.success(response.data.message)
        router.push('/')
      }
    },

    logout() {
      if (!this.token) {
        this.user = null
        router.push('/login')
        return
      }
      authRepository.logout().catch(() => {})
      this.user = null
      this.token = null
      router.push('/login')
    },

    async fetchUser() {
      try {
        const response = await authRepository.fetchCurrentUser()
        if (response.data.success) {
          this.user = response.data.data
        }
      } catch {
        this.user = null
        this.token = null
        router.push('/login')
      }
    },
  },

  persist: {
    storage: localStorage,
    paths: ['user', 'token'],
  },
})

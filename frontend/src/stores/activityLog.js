import { defineStore } from 'pinia'
import api from '@/plugins/axios'

export const useActivityLogStore = defineStore('activityLog', {
  state: () => ({
    logs: [],
    users: [],
    loading: false,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 50,
      total: 0,
    },
  }),

  actions: {
    async fetchLogs(filters = {}) {
      this.loading = true
      try {
        const params = new URLSearchParams()
        if (filters.user_id) params.append('user_id', filters.user_id)
        if (filters.action) params.append('action', filters.action)
        if (filters.model_type) params.append('model_type', filters.model_type)
        if (filters.page) params.append('page', filters.page)
        if (filters.per_page) params.append('per_page', filters.per_page)

        const response = await api.get(`/activity-logs?${params}`)
        if (response.data.success) {
          this.logs = response.data.data.data
          this.pagination = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total,
          }
        }
      } catch (error) {
        console.error('Erro ao buscar logs:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchUsers() {
      try {
        const response = await api.get('/users')
        if (response.data.success) {
          this.users = response.data.data
        }
      } catch (error) {
        console.error('Erro ao buscar usuários:', error)
      }
    },
  },
})

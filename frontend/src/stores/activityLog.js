import { defineStore } from 'pinia'
import api from '@/plugins/axios'

export const useActivityLogStore = defineStore('activityLog', {
  state: () => ({
    logs: [],
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
        if (filters.start_date) params.append('start_date', filters.start_date)
        if (filters.end_date) params.append('end_date', filters.end_date)
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
  },
})

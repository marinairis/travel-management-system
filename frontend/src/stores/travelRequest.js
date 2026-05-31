import { defineStore } from 'pinia'
import api from '@/plugins/axios'
import { ElMessage } from 'element-plus'

export const useTravelRequestStore = defineStore('travelRequest', {
  state: () => ({
    travelRequests: [],
    loading: false,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    },
    dashboardStats: {
      total: 0,
      by_status: {},
      by_travel_type: {},
      top_destinations: [],
    },
    pendingApproval: [],
    recentRequests: [],
    isLoadingDashboard: false,
  }),

  actions: {
    async fetchTravelRequests(filters = {}) {
      this.loading = true
      try {
        const params = new URLSearchParams()
        if (filters.status) params.append('status', filters.status)
        if (filters.destination) params.append('destination', filters.destination)
        if (filters.start_date) params.append('start_date', filters.start_date)
        if (filters.end_date) params.append('end_date', filters.end_date)
        params.append('per_page', filters.per_page || 10)
        params.append('page', filters.page || 1)

        const response = await api.get(`/travel-requests?${params}`)
        if (response.data.success) {
          this.travelRequests = response.data.data
          this.pagination = response.data.meta
        }
      } catch (error) {
        console.error('Erro ao buscar pedidos:', error)
      } finally {
        this.loading = false
      }
    },

    async createTravelRequest(data) {
      try {
        const response = await api.post('/travel-requests', data)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchTravelRequests()
          return true
        }
      } catch (error) {
        console.error('Erro ao criar pedido:', error)
        return false
      }
    },

    async updateTravelRequest(id, data) {
      try {
        const response = await api.put(`/travel-requests/${id}`, data)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchTravelRequests()
          return true
        }
      } catch (error) {
        console.error('Erro ao atualizar pedido:', error)
        return false
      }
    },

    async updateStatus(id, status) {
      try {
        const response = await api.patch(`/travel-requests/${id}/status`, { status })
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchTravelRequests()
          return true
        }
      } catch (error) {
        console.error('Erro ao atualizar status:', error)
        return false
      }
    },

    async cancelTravelRequest(id, reason = '') {
      try {
        const response = await api.patch(`/travel-requests/${id}/cancel`, { reason })
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchTravelRequests()
          return true
        }
      } catch (error) {
        console.error('Erro ao cancelar pedido:', error)
        return false
      }
    },

    async cancelWithReason(id, reason) {
      return this.cancelTravelRequest(id, reason)
    },

    async fetchDashboardStats() {
      try {
        const response = await api.get('/dashboard/stats')
        if (response.data.success) {
          this.dashboardStats = response.data.data
        }
      } catch (error) {
        console.error('Erro ao buscar métricas do dashboard:', error)
      }
    },

    async fetchPendingApproval() {
      this.isLoadingDashboard = true
      try {
        const response = await api.get('/dashboard/pending-approval')
        if (response.data.success) {
          this.pendingApproval = response.data.data
        }
      } catch (error) {
        console.error('Erro ao buscar pedidos aguardando aprovação:', error)
      } finally {
        this.isLoadingDashboard = false
      }
    },

    async fetchRecentRequests() {
      try {
        const response = await api.get('/dashboard/recent-requests')
        if (response.data.success) {
          this.recentRequests = response.data.data
        }
      } catch (error) {
        console.error('Erro ao buscar pedidos recentes:', error)
      }
    },
  },
})

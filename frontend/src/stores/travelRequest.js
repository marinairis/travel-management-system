import { defineStore } from 'pinia'
import api from '@/plugins/axios'
import { ElMessage } from 'element-plus'

export const useTravelRequestStore = defineStore('travelRequest', {
  state: () => ({
    travelRequests: [],
    loading: false,
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

        const response = await api.get(`/travel-requests?${params}`)
        if (response.data.success) {
          this.travelRequests = response.data.data
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

    async cancelTravelRequest(id) {
      try {
        const response = await api.patch(`/travel-requests/${id}/cancel`)
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

    async deleteTravelRequest(id) {
      try {
        const response = await api.delete(`/travel-requests/${id}`)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchTravelRequests()
          return true
        }
      } catch (error) {
        console.error('Erro ao deletar pedido:', error)
        return false
      }
    },
  },
})

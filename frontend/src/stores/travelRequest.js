import { defineStore } from 'pinia'
import * as travelRequestRepository from '@/plugins/travelRequestRepository'
import { ElMessage } from 'element-plus'

export const useTravelRequestStore = defineStore('travelRequest', {
  state: () => ({
    travelRequests: [],
    currentRequest: null,
    loading: false,
    pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
  }),

  actions: {
    async fetchTravelRequests(filters = {}) {
      this.loading = true
      try {
        const response = await travelRequestRepository.fetchAll(filters)
        if (response.data.success) {
          this.travelRequests = response.data.data
          this.pagination = response.data.meta
        }
      } catch (error) {
        console.error(error)
      } finally {
        this.loading = false
      }
    },

    async fetchRequestDetail(id) {
      this.loading = true
      try {
        const response = await travelRequestRepository.getById(id)
        if (response.data.success) {
          this.currentRequest = response.data.data
          return response.data.data
        }
        return null
      } catch {
        this.currentRequest = null
        return null
      } finally {
        this.loading = false
      }
    },

    async _mutateRequest(repositoryCall) {
      try {
        const response = await repositoryCall()
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchTravelRequests()
          return true
        }
      } catch (error) {
        console.error(error)
        return false
      }
    },

    async createTravelRequest(data) {
      return this._mutateRequest(() => travelRequestRepository.create(data))
    },
    async updateTravelRequest(id, data) {
      return this._mutateRequest(() => travelRequestRepository.update(id, data))
    },
    async updateStatus(id, status) {
      return this._mutateRequest(() => travelRequestRepository.updateStatus(id, status))
    },
    async cancelTravelRequest(id, reason = '') {
      return this._mutateRequest(() => travelRequestRepository.cancel(id, reason))
    },
  },
})

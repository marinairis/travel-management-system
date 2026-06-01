import { defineStore } from 'pinia'
import * as activityLogRepository from '@/plugins/activityLogRepository'
import * as userRepository from '@/plugins/userRepository'

export const useActivityLogStore = defineStore('activityLog', {
  state: () => ({
    logs: [],
    users: [],
    isLoading: false,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    },
  }),

  actions: {
    async fetchLogs(filters = {}) {
      this.isLoading = true
      try {
        const response = await activityLogRepository.fetchAll({
          userId: filters.user_id,
          action: filters.action,
          modelType: filters.model_type,
          perPage: filters.per_page || this.pagination.per_page,
          page: filters.page || this.pagination.current_page,
        })
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
        console.error(error)
      } finally {
        this.isLoading = false
      }
    },

    async fetchForRequest(modelId) {
      try {
        const response = await activityLogRepository.fetchForModel({
          modelId,
          modelType: 'App\\Models\\TravelRequest',
          perPage: 20,
        })
        if (response.data.success) {
          return response.data.data?.data || []
        }
        return []
      } catch {
        return []
      }
    },

    async fetchUsers() {
      try {
        const response = await userRepository.fetchBasic()
        if (response.data.success) {
          this.users = response.data.data
        }
      } catch (error) {
        console.error(error)
      }
    },
  },
})

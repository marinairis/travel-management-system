import { defineStore } from 'pinia'
import * as dashboardRepository from '@/plugins/dashboardRepository'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: {
      total: 0,
      by_status: {},
      by_travel_type: {},
      top_destinations: [],
    },
    pendingApproval: [],
    recentRequests: [],
    isLoading: false,
  }),

  actions: {
    async fetchStats() {
      try {
        const response = await dashboardRepository.fetchStats()
        if (response.data.success) {
          this.stats = response.data.data
        }
      } catch (error) {
        console.error(error)
      }
    },

    async fetchPendingApproval() {
      this.isLoading = true
      try {
        const response = await dashboardRepository.fetchPendingApproval()
        if (response.data.success) {
          this.pendingApproval = response.data.data
        }
      } catch (error) {
        console.error(error)
      } finally {
        this.isLoading = false
      }
    },

    async fetchRecentRequests() {
      try {
        const response = await dashboardRepository.fetchRecentRequests()
        if (response.data.success) {
          this.recentRequests = response.data.data
        }
      } catch (error) {
        console.error(error)
      }
    },
  },
})

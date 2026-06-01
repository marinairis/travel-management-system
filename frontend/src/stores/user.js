import { defineStore } from 'pinia'
import * as userRepository from '@/plugins/userRepository'
import { ElMessage } from 'element-plus'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    pendingInvitations: [],
    basicUsers: [],
    isLoading: false,
    pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    filters: { userType: '', email: '' },
  }),

  actions: {
    async fetchUsers(filters = {}) {
      this.isLoading = true
      try {
        const filterData = filters || this.filters
        const response = await userRepository.fetchAll({
          userType: filterData.userType,
          status: filterData.status,
          email: filterData.email,
          perPage: filters.per_page || this.pagination.per_page,
          page: filters.page || this.pagination.current_page,
        })
        if (response.data.success) {
          this.users = response.data.data
          this.pendingInvitations = response.data.pending_invitations ?? []
          this.pagination = response.data.meta
        }
      } catch (error) {
        console.error(error)
      } finally {
        this.isLoading = false
      }
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },

    clearFilters() {
      this.filters = { userType: '', email: '' }
    },

    async _mutateUser(repositoryCall) {
      try {
        const response = await repositoryCall()
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchUsers()
          return true
        }
      } catch (error) {
        console.error(error)
        return false
      }
    },

    async updateUser(id, data) { return this._mutateUser(() => userRepository.update(id, data)) },
    async deleteUser(id) { return this._mutateUser(() => userRepository.remove(id)) },
    async toggleUserStatus(id) { return this._mutateUser(() => userRepository.toggleStatus(id)) },
    async resendInvitation(id) { return this._mutateUser(() => userRepository.resendInvitation(id)) },

    async fetchBasicUsers() {
      try {
        const response = await userRepository.fetchBasic()
        if (response.data.success) this.basicUsers = response.data.data
      } catch (error) {
        console.error(error)
      }
    },

    async inviteUser(data) {
      try {
        const response = await userRepository.invite(data)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          return true
        }
      } catch (error) {
        console.error(error)
        return false
      }
    },

    async getPendingRequestsCount(userId) {
      try {
        const response = await userRepository.getPendingRequestsCount(userId)
        return response.data.success ? response.data.data.count : 0
      } catch (error) {
        console.error(error)
        return 0
      }
    },
  },
})

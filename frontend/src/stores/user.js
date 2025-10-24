import { defineStore } from 'pinia'
import api from '@/plugins/axios'
import { ElMessage } from 'element-plus'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    loading: false,
    filters: {
      userType: '',
      email: '',
    },
  }),

  actions: {
    async fetchUsers(filters = null) {
      this.loading = true
      try {
        const params = new URLSearchParams()
        const filterData = filters || this.filters

        if (filterData.userType) {
          params.append('user_type', filterData.userType)
        }

        if (filterData.email) {
          params.append('email', filterData.email)
        }

        const response = await api.get(`/users?${params.toString()}`)
        if (response.data.success) {
          this.users = response.data.data
        }
      } catch (error) {
        console.error('Erro ao buscar usuários:', error)
      } finally {
        this.loading = false
      }
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },

    clearFilters() {
      this.filters = {
        userType: '',
        email: '',
      }
    },

    async updateUser(id, data) {
      try {
        const response = await api.put(`/users/${id}`, data)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchUsers()
          return true
        }
      } catch (error) {
        console.error('Erro ao atualizar usuário:', error)
        return false
      }
    },

    async deleteUser(id) {
      try {
        const response = await api.delete(`/users/${id}`)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          this.fetchUsers()
          return true
        }
      } catch (error) {
        console.error('Erro ao deletar usuário:', error)
        return false
      }
    },
  },
})

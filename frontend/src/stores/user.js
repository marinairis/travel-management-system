import { defineStore } from 'pinia'
import api from '@/plugins/axios'
import { ElMessage } from 'element-plus'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    loading: false,
  }),

  actions: {
    async fetchUsers() {
      this.loading = true
      try {
        const response = await api.get('/users')
        if (response.data.success) {
          this.users = response.data.data
        }
      } catch (error) {
        console.error('Erro ao buscar usuários:', error)
      } finally {
        this.loading = false
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

import { defineStore } from 'pinia'
import * as authRepository from '@/plugins/authRepository'
import router from '@/router'
import { ElMessage } from 'element-plus'

export const usePasswordResetStore = defineStore('passwordReset', {
  actions: {
    async forgotPassword(email) {
      try {
        const response = await authRepository.forgotPassword(email)
        if (response.data.success) {
          ElMessage.success(response.data.message)
        }
      } catch (error) {
        throw error
      }
    },

    async resetPassword(data) {
      try {
        const response = await authRepository.resetPassword(data)
        if (response.data.success) {
          ElMessage.success(response.data.message)
          router.push('/login')
        }
      } catch (error) {
        throw error
      }
    },
  },
})

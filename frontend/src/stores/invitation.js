import { defineStore } from 'pinia'
import { useAuthStore } from './auth'
import * as invitationRepository from '@/plugins/invitationRepository'
import router from '@/router'

export const useInvitationStore = defineStore('invitation', {
  actions: {
    async fetchInvitation(token) {
      const response = await invitationRepository.fetchByToken(token)
      return response.data.success ? response.data.data : null
    },

    async acceptInvitation(token, data) {
      const response = await invitationRepository.accept(token, data)
      if (response.data.success) {
        const authStore = useAuthStore()
        authStore.$patch({ user: response.data.data.user, token: response.data.data.token })
        router.push('/')
        return true
      }
      return false
    },
  },
})

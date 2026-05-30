import { defineStore } from 'pinia'
import api from '@/plugins/axios'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    polling: null,
  }),

  actions: {
    async fetchNotifications() {
      try {
        const response = await api.get('/notifications')
        if (response.data.success) {
          this.notifications = response.data.data
          this.unreadCount = response.data.unread_count
        }
      } catch (error) {
        // Silently fail — polling should not create noise on network errors
      }
    },

    async markAsRead(id) {
      try {
        await api.patch(`/notifications/${id}/read`)
        const n = this.notifications.find((n) => n.id === id)
        if (n && !n.read_at) {
          n.read_at = new Date().toISOString()
          if (this.unreadCount > 0) this.unreadCount--
        }
      } catch (error) {
        console.error('Erro ao marcar notificação como lida:', error)
      }
    },

    async markAllAsRead() {
      try {
        await api.patch('/notifications/read-all')
        this.notifications.forEach((n) => {
          if (!n.read_at) n.read_at = new Date().toISOString()
        })
        this.unreadCount = 0
      } catch (error) {
        console.error('Erro ao marcar todas as notificações como lidas:', error)
      }
    },

    startPolling() {
      if (this.polling) return
      this.fetchNotifications()
      this.polling = setInterval(() => this.fetchNotifications(), 30000)
    },

    stopPolling() {
      if (this.polling) {
        clearInterval(this.polling)
        this.polling = null
      }
    },
  },
})

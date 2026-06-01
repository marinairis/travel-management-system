import { defineStore } from 'pinia'
import * as notificationRepository from '@/plugins/notificationRepository'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    polling: null,
  }),

  actions: {
    async fetchNotifications() {
      try {
        const response = await notificationRepository.fetchAll()
        if (response.data.success) {
          this.notifications = response.data.data
          this.unreadCount = response.data.unread_count
        }
      } catch {
        // Silently fail — polling should not create noise on network errors
      }
    },

    async markAsRead(id) {
      try {
        await notificationRepository.markAsRead(id)
        const notification = this.notifications.find((n) => n.id === id)
        if (notification && !notification.read_at) {
          notification.read_at = new Date().toISOString()
          if (this.unreadCount > 0) this.unreadCount--
        }
      } catch (error) {
        console.error(error)
      }
    },

    async markAllAsRead() {
      try {
        await notificationRepository.markAllAsRead()
        this.notifications.forEach((n) => {
          if (!n.read_at) n.read_at = new Date().toISOString()
        })
        this.unreadCount = 0
      } catch (error) {
        console.error(error)
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

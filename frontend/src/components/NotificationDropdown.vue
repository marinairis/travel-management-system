<template>
  <el-popover
    placement="bottom-end"
    :width="360"
    trigger="click"
    popper-class="notification-popover"
  >
    <template #reference>
      <el-badge :value="notificationStore.unreadCount" :hidden="notificationStore.unreadCount === 0" type="danger">
        <el-button :icon="Bell" circle class="notification-btn" />
      </el-badge>
    </template>

    <div class="notification-panel">
      <div class="notification-header">
        <span class="notification-title">{{ $t('notifications.title') }}</span>
        <el-button
          v-if="notificationStore.unreadCount > 0"
          text
          size="small"
          @click="notificationStore.markAllAsRead()"
        >
          {{ $t('notifications.markAllRead') }}
        </el-button>
      </div>

      <div class="notification-list" v-if="notificationStore.notifications.length > 0">
        <div
          v-for="n in notificationStore.notifications"
          :key="n.id"
          class="notification-item"
          :class="{ unread: !n.read_at }"
          @click="handleNotificationClick(n)"
        >
          <div class="notification-icon">
            <el-icon :color="n.data.new_status === 'approved' ? 'var(--el-color-success)' : 'var(--el-color-danger)'">
              <component :is="n.data.new_status === 'approved' ? CircleCheck : CircleClose" />
            </el-icon>
          </div>
          <div class="notification-content">
            <p class="notification-text">
              {{
                $t('notifications.statusChanged', {
                  destination: n.data.destination,
                  status: $t('notifications.' + n.data.new_status),
                })
              }}
            </p>
            <span class="notification-time">{{ formatTime(n.created_at) }}</span>
          </div>
          <div v-if="!n.read_at" class="unread-dot" />
        </div>
      </div>

      <div v-else class="notification-empty">
        <el-icon size="32" color="var(--el-text-color-placeholder)"><Bell /></el-icon>
        <p>{{ $t('notifications.empty') }}</p>
      </div>
    </div>
  </el-popover>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { Bell, CircleCheck, CircleClose } from '@element-plus/icons-vue'
import { useNotificationStore } from '@/stores/notification'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const notificationStore = useNotificationStore()

onMounted(() => {
  notificationStore.startPolling()
})

onUnmounted(() => {
  notificationStore.stopPolling()
})

const handleNotificationClick = (n) => {
  if (!n.read_at) {
    notificationStore.markAsRead(n.id)
  }
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.notification-btn {
  width: 36px;
  height: 36px;
}

.notification-panel {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.notification-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 4px 12px;
  border-bottom: 1px solid var(--el-border-color-lighter);
}

.notification-title {
  font-weight: 600;
  font-size: 14px;
  color: var(--el-text-color-primary);
}

.notification-list {
  max-height: 360px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 4px;
  border-bottom: 1px solid var(--el-border-color-lighter);
  cursor: pointer;
  transition: background 0.2s;
  border-radius: 4px;
}

.notification-item:hover {
  background: var(--el-fill-color-light);
}

.notification-item.unread {
  background: var(--el-color-primary-light-9);
}

.notification-icon {
  flex-shrink: 0;
  margin-top: 2px;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-text {
  margin: 0 0 4px;
  font-size: 13px;
  color: var(--el-text-color-primary);
  line-height: 1.4;
}

.notification-time {
  font-size: 11px;
  color: var(--el-text-color-placeholder);
}

.unread-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--el-color-primary);
  flex-shrink: 0;
  margin-top: 6px;
}

.notification-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 32px 0;
  color: var(--el-text-color-placeholder);
  font-size: 13px;
}

.notification-empty p {
  margin: 0;
}
</style>

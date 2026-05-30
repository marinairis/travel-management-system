<template>
  <div>
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('notifications.title') }}</h1>
        <p class="voa-page-sub">{{ $t('notifications.subtitle') }}</p>
      </div>
      <el-button
        v-if="notificationStore.notifications.some(n => !n.read_at)"
        @click="notificationStore.markAllAsRead()"
      >
        ✓ {{ $t('notifications.markAllRead') }}
      </el-button>
    </div>

    <el-card shadow="never" style="padding:0;overflow:hidden">
      <el-empty
        v-if="notificationStore.notifications.length === 0"
        :description="$t('notifications.empty')"
        style="padding:32px"
      />
      <div
        v-for="n in notificationStore.notifications"
        :key="n.id"
        :class="['voa-notif-item', !n.read_at ? 'unread' : '']"
        @click="openRequest(n)"
      >
        <div :class="['voa-notif-ic', n.type]">
          {{ n.type === 'approved' ? '✓' : '✕' }}
        </div>
        <div style="flex:1">
          <div style="font-size:14px">
            <strong>{{ n.data?.travel_request?.destination || n.data?.destination || '' }}</strong>
            — {{ n.type === 'approved' ? $t('notifications.approved') : $t('notifications.cancelled') }}
          </div>
          <div style="font-size:12px;color:var(--el-text-color-secondary);margin-top:2px">
            {{ formatDate(n.created_at) }}
          </div>
        </div>
        <span
          v-if="!n.read_at"
          style="width:8px;height:8px;border-radius:50%;background:var(--el-color-primary);flex-shrink:0"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'

const router = useRouter()
const notificationStore = useNotificationStore()

const formatDate = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleString('pt-BR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const openRequest = async (n) => {
  if (!n.read_at) await notificationStore.markAsRead(n.id)
  const requestId = n.data?.travel_request_id || n.data?.id
  if (requestId) router.push('/requests/' + requestId)
}

onMounted(() => {
  notificationStore.fetchNotifications()
})
</script>

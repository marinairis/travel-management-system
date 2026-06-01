<template>
  <div class="voa-app">
    <div :class="['voa-backdrop', sidebarOpen ? 'is-open' : '']" @click="sidebarOpen = false" />

    <el-container height="100vh">
      <el-aside :class="['voa-aside', sidebarOpen ? 'is-open' : '']" width="248px">
        <div style="display: flex; flex-direction: column; height: 100%; overflow: hidden">
          <div class="voa-brand">
            <el-icon :size="36" class="voa-brand-name">
              <Place />
            </el-icon>
            <div>
              <div class="voa-brand-name">{{ $t('auth.title') }}</div>
              <div class="voa-brand-tag">{{ $t('navigation.tagline') }}</div>
            </div>
          </div>

          <el-menu
            :default-active="activeMenu"
            style="flex: 1; overflow-y: auto; border-right: none; background: transparent"
            @select="handleMenuSelect"
          >
            <div class="voa-nav-section">{{ $t('navigation.sectionMain') }}</div>

            <el-menu-item index="dashboard" class="voa-nav-item">
              <el-icon><House /></el-icon>
              <template #title>{{ $t('navigation.dashboard') }}</template>
            </el-menu-item>

            <el-menu-item index="requests" class="voa-nav-item">
              <el-icon><Document /></el-icon>
              <template #title>{{ $t('navigation.travelRequests') }}</template>
            </el-menu-item>

            <el-menu-item index="notifications" class="voa-nav-item">
              <el-icon><Bell /></el-icon>
              <template #title>
                <span>{{ $t('navigation.notifications') }}</span>
                <el-badge
                  v-if="notificationStore.unreadCount > 0"
                  :value="notificationStore.unreadCount"
                  style="margin-left: auto"
                />
              </template>
            </el-menu-item>

            <el-menu-item index="settings" class="voa-nav-item">
              <el-icon><Setting /></el-icon>
              <template #title>{{ $t('navigation.settings') }}</template>
            </el-menu-item>

            <template v-if="authStore.isAdmin">
              <div class="voa-nav-section">{{ $t('navigation.sectionAdmin') }}</div>

              <el-menu-item index="users" class="voa-nav-item">
                <el-icon><UserFilled /></el-icon>
                <template #title>{{ $t('navigation.users') }}</template>
              </el-menu-item>

              <el-menu-item index="logs" class="voa-nav-item">
                <el-icon><List /></el-icon>
                <template #title>{{ $t('navigation.activityLogs') }}</template>
              </el-menu-item>
            </template>
          </el-menu>

          <div class="voa-sidebar-footer">
            <div class="voa-user-chip" @click="handleMenuSelect('settings')">
              <el-avatar
                :size="32"
                :style="{
                  background: avatarBg(authStore.user?.id || 'x'),
                  color: '#fff',
                  fontSize: '12px',
                  fontWeight: 700,
                }"
              >
                {{ initials(authStore.user?.name || '?') }}
              </el-avatar>
              <div style="flex: 1; min-width: 0">
                <div class="voa-user-name">{{ authStore.user?.name }}</div>
                <div class="voa-user-role">{{ roleLabel }}</div>
              </div>
            </div>
          </div>
        </div>
      </el-aside>

      <el-container class="voa-right-container" direction="vertical">
        <el-header class="voa-header">
          <TheHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />
        </el-header>

        <el-main class="voa-main">
          <div class="voa-content-inner">
            <router-view />
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { useRouter, useRoute } from 'vue-router'
import TheHeader from '@/components/TheHeader.vue'
import { House, Document, Bell, Setting, UserFilled, List } from '@element-plus/icons-vue'
import { useAvatar } from '@/composables/useAvatar'
import { useRole } from '@/composables/useRole'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()
const route = useRoute()

const sidebarOpen = ref(false)
const isMobile = ref(false)

const activeMenu = computed(() => {
  const path = route.path
  if (path.startsWith('/requests')) return 'requests'
  if (path.includes('/users')) return 'users'
  if (path.includes('/logs')) return 'logs'
  if (path.includes('/notifications')) return 'notifications'
  if (path.includes('/settings')) return 'settings'
  if (path.includes('/dashboard') || path === '/') return 'dashboard'
  return 'dashboard'
})

const { initials, avatarBg } = useAvatar()
const { currentUserRoleLabel: roleLabel } = useRole()

const handleMenuSelect = (index) => {
  if (isMobile.value) sidebarOpen.value = false
  const routes = {
    dashboard: '/dashboard',
    requests: '/requests',
    notifications: '/notifications',
    settings: '/settings',
    users: '/users',
    logs: '/logs',
  }
  if (routes[index]) router.push(routes[index])
}

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768
  if (!isMobile.value) sidebarOpen.value = false
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  notificationStore.startPolling()
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.voa-right-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.voa-header {
  height: 64px !important;
  flex-shrink: 0;
  background: var(--el-bg-color) !important;
  border-bottom: 1px solid var(--el-border-color) !important;
  padding: 0 20px !important;
  display: flex !important;
  align-items: center !important;
}

.voa-main {
  flex: 1;
  padding: 0 !important;
  background: var(--el-bg-color-page) !important;
  overflow-y: auto !important;
}

.voa-content-inner {
  padding: 26px 32px;
  width: 100%;
  max-width: 1366px;
  margin: 0 auto;
}

@media (max-width: 768px) {
  .voa-content-inner {
    padding: 16px;
  }
}
</style>

<template>
  <div class="voa-app">
    <!-- Mobile backdrop -->
    <div :class="['voa-backdrop', sidebarOpen ? 'is-open' : '']" @click="sidebarOpen = false" />

    <!-- Sidebar -->
    <el-aside :class="['voa-aside', sidebarOpen ? 'is-open' : '']" :width="sidebarWidth">
      <div style="display:flex;flex-direction:column;height:100%;overflow:hidden">
        <!-- Brand -->
        <div class="voa-brand">
          <div class="voa-brand-icon">✈</div>
          <div>
            <div class="voa-brand-name">{{ $t('auth.title') }}</div>
            <div class="voa-brand-tag">{{ $t('navigation.tagline') }}</div>
          </div>
        </div>

        <!-- Navigation -->
        <el-menu
          :default-active="activeMenu"
          style="flex:1;overflow-y:auto;border-right:none;background:transparent"
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
                style="margin-left:auto"
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

        <!-- User chip -->
        <div class="voa-sidebar-footer">
          <div class="voa-user-chip" @click="handleMenuSelect('settings')">
            <el-avatar
              :size="32"
              :style="{ background: avatarBg(authStore.user?.id || 'x'), color: '#fff', fontSize: '12px', fontWeight: 700 }"
            >
              {{ initials(authStore.user?.name || '?') }}
            </el-avatar>
            <div style="flex:1;min-width:0">
              <div class="voa-user-name">{{ authStore.user?.name }}</div>
              <div class="voa-user-role">{{ roleLabel }}</div>
            </div>
          </div>
        </div>
      </div>
    </el-aside>

    <!-- Fixed header -->
    <el-header class="voa-header" height="64px">
      <TheHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />
    </el-header>

    <!-- Scrollable main content -->
    <div class="voa-main voa-main-wrapper">
      <div class="voa-content-inner">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import TheHeader from '@/components/TheHeader.vue'
import { House, Document, Bell, Setting, UserFilled, List } from '@element-plus/icons-vue'

const { t } = useI18n()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()
const route = useRoute()

const sidebarOpen = ref(false)
const isMobile = ref(false)

const sidebarWidth = computed(() => isMobile.value ? '248px' : '248px')

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

const roleLabel = computed(() => {
  if (authStore.isAdmin) return t('users.roleAdmin')
  if (authStore.isManager) return t('users.roleManager')
  return t('users.roleRequester')
})

function initials(name) {
  const parts = String(name).trim().split(' ')
  return ((parts[0]?.[0] || '') + (parts[parts.length - 1]?.[0] || '')).toUpperCase()
}

function avatarBg(id) {
  const c = ['#4D7A46', '#6B8E5A', '#5A7A6E', '#7A6E5A', '#6E5A7A', '#5A6E7A']
  let h = 0
  const s = String(id)
  for (let i = 0; i < s.length; i++) h = s.charCodeAt(i) + ((h << 5) - h)
  return c[Math.abs(h) % c.length]
}

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

const handleToggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  window.addEventListener('toggle-sidebar', handleToggleSidebar)
  notificationStore.startPolling()
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  window.removeEventListener('toggle-sidebar', handleToggleSidebar)
})
</script>

<style scoped>
.voa-main-wrapper {
  margin-left: var(--sidebar-width);
}

@media (max-width: 768px) {
  .voa-main-wrapper {
    margin-left: 0 !important;
  }
}
</style>

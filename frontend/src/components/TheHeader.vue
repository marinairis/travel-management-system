<template>
  <div style="display:flex;align-items:center;width:100%;gap:10px">
    <!-- Mobile hamburger -->
    <el-button
      v-if="authStore.isAuthenticated"
      plain
      size="small"
      class="voa-hamburger"
      @click="$emit('toggleSidebar')"
    >
      <el-icon><Menu /></el-icon>
    </el-button>

    <!-- Search -->
    <el-input
      :placeholder="$t('common.search')"
      size="default"
      clearable
      class="voa-tb-search"
      style="flex:1;max-width:300px"
    >
      <template #prefix><el-icon><Search /></el-icon></template>
    </el-input>

    <!-- Right actions -->
    <div class="voa-tb-actions">
      <!-- Notifications bell -->
      <el-badge
        v-if="authStore.isAuthenticated"
        :value="notificationStore.unreadCount"
        :hidden="notificationStore.unreadCount === 0"
      >
        <el-button plain size="small" circle @click="router.push('/notifications')">
          <el-icon><Bell /></el-icon>
        </el-button>
      </el-badge>

      <!-- Theme cycle -->
      <el-tooltip :content="$t('common.toggleTheme')" placement="bottom">
        <el-button plain size="small" circle @click="cycleTheme">
          <el-icon v-if="themeStore.isDark"><Sunny /></el-icon>
          <el-icon v-else><Moon /></el-icon>
        </el-button>
      </el-tooltip>

      <!-- Language dropdown -->
      <el-dropdown v-if="authStore.isAuthenticated" trigger="click" @command="setLocale">
        <el-button size="small" plain>
          {{ currentLocaleLabel }} ▾
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item
              v-for="l in locales"
              :key="l.code"
              :command="l.code"
            >
              <span style="font-size:12px;color:var(--el-text-color-secondary);margin-right:8px">{{ l.flag }}</span>
              {{ l.label }}
              <span
                v-if="localeStore.currentLocale === l.code"
                style="margin-left:auto;color:var(--el-color-primary);padding-left:8px"
              >✓</span>
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>

      <!-- User avatar dropdown -->
      <el-dropdown v-if="authStore.isAuthenticated" trigger="click" @command="handleUserCommand">
        <el-avatar
          :size="34"
          :style="{ background: avatarBg(authStore.user?.id || 'x'), color: '#fff', fontSize: '12px', fontWeight: 700, cursor: 'pointer' }"
        >
          {{ initials(authStore.user?.name || '?') }}
        </el-avatar>
        <template #dropdown>
          <el-dropdown-menu>
            <div style="padding:10px 14px;border-bottom:1px solid var(--el-border-color)">
              <div style="font-weight:600;font-size:14px">{{ authStore.user?.name }}</div>
              <div style="font-size:12px;color:var(--el-text-color-secondary)">{{ authStore.user?.email }}</div>
              <el-tag :type="roleTagType" size="small" style="margin-top:6px">{{ roleLabel }}</el-tag>
            </div>
            <el-dropdown-item command="settings">
              <el-icon><Setting /></el-icon>
              {{ $t('navigation.settings') }}
            </el-dropdown-item>
            <el-dropdown-item divided command="logout" style="color:var(--el-color-danger)">
              <el-icon><SwitchButton /></el-icon>
              {{ $t('common.logout') }}
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useNotificationStore } from '@/stores/notification'
import { useLocaleStore } from '@/stores/locale'
import { useI18n } from 'vue-i18n'
import { Menu, Search, Bell, Moon, Sunny, Setting, SwitchButton } from '@element-plus/icons-vue'

defineEmits(['toggleSidebar'])

const { locale } = useI18n()
const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const notificationStore = useNotificationStore()
const localeStore = useLocaleStore()

const locales = [
  { code: 'pt-BR', label: 'Português (BR)', flag: '🇧🇷' },
  { code: 'en', label: 'English', flag: '🇺🇸' },
  { code: 'es', label: 'Español', flag: '🇪🇸' },
]

const currentLocaleLabel = computed(() => {
  const map = { 'pt-BR': 'PT', 'en': 'EN', 'es': 'ES' }
  return map[localeStore.currentLocale] || 'PT'
})

const roleLabel = computed(() => {
  if (authStore.isAdmin) return 'Admin'
  if (authStore.isManager) return 'Gestor'
  return 'Solicitante'
})

const roleTagType = computed(() => {
  if (authStore.isAdmin) return 'danger'
  if (authStore.isManager) return 'warning'
  return 'info'
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

const cycleTheme = () => {
  themeStore.toggleTheme()
}

const setLocale = (code) => {
  localeStore.setLocale(code)
  locale.value = code
}

const handleUserCommand = (cmd) => {
  if (cmd === 'logout') {
    authStore.logout()
  } else if (cmd === 'settings') {
    router.push('/settings')
  }
}
</script>

<style scoped>
.voa-hamburger {
  display: none;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .voa-hamburger {
    display: flex;
  }
}
</style>

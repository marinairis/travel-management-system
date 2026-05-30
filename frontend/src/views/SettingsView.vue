<template>
  <div>
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('settings.title') }}</h1>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:200px 1fr;gap:18px;align-items:start">
      <!-- Tab sidebar -->
      <el-card shadow="never" style="padding:0">
        <el-menu :default-active="activeTab" @select="activeTab = $event" style="border-right:none">
          <el-menu-item index="profile">{{ $t('settings.profileTab') }}</el-menu-item>
          <el-menu-item index="appearance">{{ $t('settings.appearanceTab') }}</el-menu-item>
          <el-menu-item index="language">{{ $t('settings.languageTab') }}</el-menu-item>
          <el-menu-item index="notifications">{{ $t('settings.notificationsTab') }}</el-menu-item>
        </el-menu>
      </el-card>

      <!-- Tab content -->
      <el-card shadow="never">
        <!-- Profile -->
        <div v-if="activeTab === 'profile'">
          <div style="display:flex;align-items:center;gap:14px;padding-bottom:18px;margin-bottom:4px;border-bottom:1px solid var(--el-border-color)">
            <el-avatar
              :size="52"
              :style="{ background: avatarBg(authStore.user?.id || 'x'), color: '#fff', fontSize: '14px', fontWeight: 700 }"
            >
              {{ initials(authStore.user?.name || '?') }}
            </el-avatar>
            <div>
              <div style="font-weight:700;font-size:16px">{{ authStore.user?.name }}</div>
              <div style="color:var(--el-text-color-secondary);font-size:13px;margin-top:2px">{{ authStore.user?.email }}</div>
              <el-tag :type="roleTagType" size="small" style="margin-top:6px">{{ roleLabel }}</el-tag>
            </div>
          </div>
          <div style="margin-top:16px;display:flex;flex-direction:column;gap:14px">
            <div>
              <div style="font-size:13px;font-weight:500;margin-bottom:5px;color:var(--el-text-color-regular)">{{ $t('settings.name') }}</div>
              <el-input :model-value="authStore.user?.name" disabled />
            </div>
            <div>
              <div style="font-size:13px;font-weight:500;margin-bottom:5px;color:var(--el-text-color-regular)">{{ $t('settings.email') }}</div>
              <el-input :model-value="authStore.user?.email" disabled />
            </div>
            <div>
              <div style="font-size:13px;font-weight:500;margin-bottom:5px;color:var(--el-text-color-regular)">{{ $t('settings.role') }}</div>
              <el-input :model-value="roleLabel" disabled />
            </div>
          </div>
        </div>

        <!-- Appearance -->
        <div v-else-if="activeTab === 'appearance'">
          <div style="font-weight:700;margin-bottom:14px">{{ $t('settings.appearanceTab') }}</div>
          <div style="display:flex;flex-direction:column;gap:8px;max-width:280px">
            <div
              :class="['voa-theme-opt', !themeStore.isDark ? 'active' : '']"
              @click="setTheme(false)"
            >
              <span>☀ {{ $t('settings.themeLight') }}</span>
              <span v-if="!themeStore.isDark" style="margin-left:auto;color:var(--el-color-primary)">✓</span>
            </div>
            <div
              :class="['voa-theme-opt', themeStore.isDark ? 'active' : '']"
              @click="setTheme(true)"
            >
              <span>🌙 {{ $t('settings.themeDark') }}</span>
              <span v-if="themeStore.isDark" style="margin-left:auto;color:var(--el-color-primary)">✓</span>
            </div>
          </div>
        </div>

        <!-- Language -->
        <div v-else-if="activeTab === 'language'">
          <div style="font-weight:700;margin-bottom:14px">{{ $t('settings.languageTab') }}</div>
          <div style="display:flex;flex-direction:column;gap:8px;max-width:280px">
            <div
              v-for="l in locales"
              :key="l.code"
              :class="['voa-lang-opt', localeStore.currentLocale === l.code ? 'active' : '']"
              @click="setLocale(l.code)"
            >
              <span style="font-size:18px">{{ l.flag }}</span>
              {{ l.label }}
              <span v-if="localeStore.currentLocale === l.code" style="margin-left:auto;color:var(--el-color-primary)">✓</span>
            </div>
          </div>
        </div>

        <!-- Notifications -->
        <div v-else-if="activeTab === 'notifications'">
          <div class="voa-notif-setting">
            <div>
              <div style="font-weight:600">{{ $t('settings.notifPush') }}</div>
              <div style="font-size:13px;color:var(--el-text-color-secondary)">{{ $t('settings.notifPushDesc') }}</div>
            </div>
            <el-switch v-model="notifPush" />
          </div>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useLocaleStore } from '@/stores/locale'

const { t, locale } = useI18n()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const localeStore = useLocaleStore()

const activeTab = ref('profile')
const notifPush = ref(true)

const locales = [
  { code: 'pt-BR', label: 'Português (BR)', flag: '🇧🇷' },
  { code: 'en', label: 'English', flag: '🇺🇸' },
  { code: 'es', label: 'Español', flag: '🇪🇸' },
]

const roleLabel = computed(() => {
  if (authStore.isAdmin) return t('users.roleAdmin')
  if (authStore.isManager) return t('users.roleManager')
  return t('users.roleRequester')
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
  const c = [
    'var(--avatar-color-1)',
    'var(--avatar-color-2)',
    'var(--avatar-color-3)',
    'var(--avatar-color-4)',
    'var(--avatar-color-5)',
    'var(--avatar-color-6)'
  ]
  let h = 0
  const s = String(id)
  for (let i = 0; i < s.length; i++) h = s.charCodeAt(i) + ((h << 5) - h)
  return c[Math.abs(h) % c.length]
}

const setTheme = (dark) => {
  if (themeStore.isDark !== dark) themeStore.toggleTheme()
}

const setLocale = (code) => {
  localeStore.setLocale(code)
  locale.value = code
}
</script>

<style scoped>
@media (max-width: 768px) {
  div[style*="grid-template-columns:200px"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

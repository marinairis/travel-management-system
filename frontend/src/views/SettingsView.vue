<template>
  <div>
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('settings.title') }}</h1>
      </div>
    </div>

    <div class="settings-layout">
      <el-card shadow="never" class="settings-nav-card">
        <el-menu :default-active="activeTab" @select="activeTab = $event" class="settings-nav-menu">
          <el-menu-item index="profile">{{ $t('settings.profileTab') }}</el-menu-item>
          <el-menu-item index="appearance">{{ $t('settings.appearanceTab') }}</el-menu-item>
          <el-menu-item index="language">{{ $t('settings.languageTab') }}</el-menu-item>
          <el-menu-item index="notifications">{{ $t('settings.notificationsTab') }}</el-menu-item>
        </el-menu>
      </el-card>

      <el-card shadow="never">
        <div v-if="activeTab === 'profile'">
          <div class="settings-profile-header">
            <el-avatar
              :size="52"
              :style="{ background: avatarBg(authStore.user?.id || 'x'), color: '#fff', fontSize: '14px', fontWeight: 700 }"
            >
              {{ initials(authStore.user?.name || '?') }}
            </el-avatar>
            <div>
              <div class="settings-profile-name">{{ authStore.user?.name }}</div>
              <div class="settings-profile-email">{{ authStore.user?.email }}</div>
              <el-tag :type="roleTagType" size="small" class="settings-profile-tag">{{ roleLabel }}</el-tag>
            </div>
          </div>
          <div class="settings-fields">
            <div>
              <div class="settings-field-label">{{ $t('settings.name') }}</div>
              <el-input :model-value="authStore.user?.name" disabled />
            </div>
            <div>
              <div class="settings-field-label">{{ $t('settings.email') }}</div>
              <el-input :model-value="authStore.user?.email" disabled />
            </div>
            <div>
              <div class="settings-field-label">{{ $t('settings.role') }}</div>
              <el-input :model-value="roleLabel" disabled />
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'appearance'">
          <div class="settings-section-title">{{ $t('settings.appearanceTab') }}</div>
          <div class="settings-options-list">
            <div
              :class="['voa-theme-opt', !themeStore.isDark ? 'active' : '']"
              @click="setTheme(false)"
            >
              <span>☀ {{ $t('settings.themeLight') }}</span>
              <span v-if="!themeStore.isDark" class="settings-option-check">✓</span>
            </div>
            <div
              :class="['voa-theme-opt', themeStore.isDark ? 'active' : '']"
              @click="setTheme(true)"
            >
              <span>🌙 {{ $t('settings.themeDark') }}</span>
              <span v-if="themeStore.isDark" class="settings-option-check">✓</span>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'language'">
          <div class="settings-section-title">{{ $t('settings.languageTab') }}</div>
          <div class="settings-options-list">
            <div
              v-for="l in locales"
              :key="l.code"
              :class="['voa-lang-opt', localeStore.currentLocale === l.code ? 'active' : '']"
              @click="setLocale(l.code)"
            >
              <span class="settings-locale-flag">{{ l.flag }}</span>
              {{ l.label }}
              <span v-if="localeStore.currentLocale === l.code" class="settings-option-check">✓</span>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'notifications'">
          <div class="voa-notif-setting">
            <div>
              <div class="settings-notif-title">{{ $t('settings.notifPush') }}</div>
              <div class="settings-notif-desc">{{ $t('settings.notifPushDesc') }}</div>
            </div>
            <el-switch v-model="notifPush" />
          </div>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useLocaleStore } from '@/stores/locale'
import { useAvatar } from '@/composables/useAvatar'
import { useRole } from '@/composables/useRole'

const { locale } = useI18n()
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

const { initials, avatarBg } = useAvatar()
const { currentUserRoleLabel: roleLabel, currentUserRoleTagType: roleTagType } = useRole()

const setTheme = (dark) => {
  if (themeStore.isDark !== dark) themeStore.toggleTheme()
}

const setLocale = (code) => {
  localeStore.setLocale(code)
  locale.value = code
}
</script>

<style scoped>
.settings-layout {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 18px;
  align-items: start;
}

.settings-nav-card :deep(.el-card__body) {
  padding: 0;
}

.settings-nav-menu {
  border-right: none;
}

.settings-profile-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding-bottom: 18px;
  margin-bottom: 4px;
  border-bottom: 1px solid var(--el-border-color);
}

.settings-profile-name {
  font-weight: 700;
  font-size: 16px;
}

.settings-profile-email {
  color: var(--el-text-color-secondary);
  font-size: 13px;
  margin-top: 2px;
}

.settings-profile-tag {
  margin-top: 6px;
}

.settings-fields {
  margin-top: 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.settings-field-label {
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 5px;
  color: var(--el-text-color-regular);
}

.settings-section-title {
  font-weight: 700;
  margin-bottom: 14px;
}

.settings-options-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-width: 280px;
}

.settings-option-check {
  margin-left: auto;
  color: var(--el-color-primary);
}

.settings-locale-flag {
  font-size: 18px;
}

.settings-notif-title {
  font-weight: 600;
}

.settings-notif-desc {
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

@media (max-width: 768px) {
  .settings-layout {
    grid-template-columns: 1fr;
  }
}
</style>

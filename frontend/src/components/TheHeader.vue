<template>
  <el-header class="app-header">
    <div class="header-left">
      <el-button
        v-if="authStore.isAuthenticated"
        class="sidebar-toggle"
        circle
        @click="toggleSidebar"
        :icon="Menu"
      />
      <el-icon class="logo-icon" :size="24">
        <MapLocation />
      </el-icon>

      <h1 class="app-title" :class="{ 'dark-theme': themeStore.isDark }">{{ $t('auth.title') }}</h1>
    </div>

    <div class="header-right">
      <el-tooltip :content="$t('common.toggleTheme')" placement="bottom">
        <el-button circle @click="toggleTheme" :icon="themeStore.isDark ? Sunny : Moon" />
      </el-tooltip>

      <el-dropdown v-if="authStore.isAuthenticated">
        <el-button circle>
          <el-icon><User /></el-icon>
        </el-button>

        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item disabled>
              <div class="user-info">
                <div class="user-name">{{ authStore.user?.name }}</div>

                <div class="user-email">{{ authStore.user?.email }}</div>

                <div class="user-role">
                  {{ authStore.isAdmin ? $t('users.admin') : $t('users.basic') }}
                </div>
              </div>
            </el-dropdown-item>

            <el-dropdown-item divided @click="handleLogout">
              <el-icon><SwitchButton /></el-icon>
              {{ $t('common.logout') }}
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </el-header>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useRouter } from 'vue-router'
import { User, SwitchButton, Moon, Sunny, MapLocation, Menu } from '@element-plus/icons-vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const toggleTheme = () => {
  themeStore.toggleTheme()
}

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

const toggleSidebar = () => {
  window.dispatchEvent(new CustomEvent('toggle-sidebar'))
}
</script>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 var(--spacing-xl);
  box-shadow: var(--shadow-md);
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: var(--z-header);
  background-color: var(--el-bg-color);
  height: var(--header-height);
}

.header-left {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.logo-icon {
  color: var(--el-color-primary);
}

.app-title {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
  color: var(--el-text-color-primary);
  transition: color 0.3s ease;
}

.app-title.dark-theme {
  color: #ffffff;
}

.header-right {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.sidebar-toggle {
  display: none;
}

@media (max-width: 768px) {
  .sidebar-toggle {
    display: flex;
  }

  .app-title {
    font-size: 16px;
  }
}
</style>

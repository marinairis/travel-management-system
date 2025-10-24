<template>
  <el-header class="app-header">
    <div class="header-left">
      <el-icon class="logo-icon" :size="24">
        <MapLocation />
      </el-icon>
      <h1 class="app-title">Viagens Corporativas</h1>
    </div>

    <div class="header-right">
      <el-tooltip content="Alternar Tema" placement="bottom">
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
                  {{ authStore.isAdmin ? 'Administrador' : 'Usuário Básico' }}
                </div>
              </div>
            </el-dropdown-item>
            <el-dropdown-item divided @click="handleLogout">
              <el-icon><SwitchButton /></el-icon>
              Sair
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
import { User, SwitchButton, Moon, Sunny, MapLocation } from '@element-plus/icons-vue'

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
}

.header-right {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

/* User Info já está em utilities.css */

@media (max-width: 768px) {
  .app-title {
    font-size: 16px;
  }
}
</style>

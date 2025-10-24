<template>
  <el-header class="app-header">
    <div class="header-left">
      <el-icon class="logo-icon" :size="24">
        <Van />
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
              <strong>{{ authStore.user?.name }}</strong>
            </el-dropdown-item>
            <el-dropdown-item divided v-if="authStore.isAdmin" @click="goToUsers">
              <el-icon><UserFilled /></el-icon>
              Gestão de Usuários
            </el-dropdown-item>
            <el-dropdown-item v-if="authStore.isAdmin" @click="goToLogs">
              <el-icon><Document /></el-icon>
              Logs de Atividades
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
import { User, SwitchButton, Moon, Sunny, Van, UserFilled, Document } from '@element-plus/icons-vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const toggleTheme = () => {
  themeStore.toggleTheme()
}

const handleLogout = () => {
  authStore.logout()
}

const goToUsers = () => {
  router.push('/users')
}

const goToLogs = () => {
  router.push('/logs')
}
</script>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
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
  gap: 12px;
}

@media (max-width: 768px) {
  .app-title {
    font-size: 16px;
  }
}
</style>

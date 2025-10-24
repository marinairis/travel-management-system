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

      <el-tooltip content="Menu de Navegação" placement="bottom">
        <el-button circle @click="drawerVisible = true" v-if="authStore.isAuthenticated">
          <el-icon><Menu /></el-icon>
        </el-button>
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

    <!-- Navigation Drawer -->
    <el-drawer
      v-model="drawerVisible"
      title="Menu de Navegação"
      direction="rtl"
      size="300px"
      :with-header="true"
    >
      <div class="drawer-content">
        <div class="user-section">
          <div class="user-avatar">
            <el-icon :size="40"><User /></el-icon>
          </div>
          <div class="user-details">
            <h3>{{ authStore.user?.name }}</h3>
            <p>{{ authStore.user?.email }}</p>
            <el-tag :type="authStore.isAdmin ? 'danger' : 'primary'" size="small">
              {{ authStore.isAdmin ? 'Administrador' : 'Usuário Básico' }}
            </el-tag>
          </div>
        </div>

        <el-divider />

        <div class="navigation-section" v-if="authStore.isAdmin">
          <h4>Administração</h4>
          <el-menu :default-active="activeMenu" class="admin-menu" @select="handleMenuSelect">
            <el-menu-item index="users">
              <el-icon><UserFilled /></el-icon>
              <span>Gestão de Usuários</span>
            </el-menu-item>
            <el-menu-item index="logs">
              <el-icon><Document /></el-icon>
              <span>Logs de Atividades</span>
            </el-menu-item>
          </el-menu>
        </div>

        <div class="navigation-section">
          <h4>Navegação</h4>
          <el-menu :default-active="activeMenu" class="main-menu" @select="handleMenuSelect">
            <el-menu-item index="dashboard">
              <el-icon><House /></el-icon>
              <span>Dashboard</span>
            </el-menu-item>
          </el-menu>
        </div>
      </div>
    </el-drawer>
  </el-header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useRouter, useRoute } from 'vue-router'
import {
  User,
  SwitchButton,
  Moon,
  Sunny,
  Van,
  UserFilled,
  Document,
  Menu,
  House,
} from '@element-plus/icons-vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()
const route = useRoute()

const drawerVisible = ref(false)

const activeMenu = computed(() => {
  const path = route.path
  if (path.includes('/users')) return 'users'
  if (path.includes('/logs')) return 'logs'
  if (path.includes('/dashboard') || path === '/') return 'dashboard'
  return ''
})

const toggleTheme = () => {
  themeStore.toggleTheme()
}

const handleLogout = () => {
  authStore.logout()
  drawerVisible.value = false
}

const handleMenuSelect = (index) => {
  drawerVisible.value = false

  switch (index) {
    case 'dashboard':
      router.push('/dashboard')
      break
    case 'users':
      router.push('/users')
      break
    case 'logs':
      router.push('/logs')
      break
  }
}
</script>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  width: 100%;
  position: relative;
  z-index: 1000;
  background-color: var(--el-bg-color);
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

/* User Info Styles */
.user-info {
  text-align: center;
  padding: 8px 0;
}

.user-name {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 4px;
}

.user-email {
  font-size: 12px;
  color: var(--el-text-color-secondary);
  margin-bottom: 4px;
}

.user-role {
  font-size: 11px;
  color: var(--el-color-primary);
  font-weight: 500;
}

/* Drawer Styles */
.drawer-content {
  padding: 0;
}

.user-section {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: var(--el-bg-color-page);
  border-radius: 8px;
  margin-bottom: 20px;
}

.user-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--el-color-primary-light-9);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--el-color-primary);
}

.user-details h3 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 600;
}

.user-details p {
  margin: 0 0 8px 0;
  font-size: 14px;
  color: var(--el-text-color-secondary);
}

.navigation-section {
  margin-bottom: 24px;
}

.navigation-section h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--el-text-color-regular);
  padding: 0 20px;
}

.admin-menu,
.main-menu {
  border: none;
  background: transparent;
}

.admin-menu .el-menu-item,
.main-menu .el-menu-item {
  height: 48px;
  line-height: 48px;
  padding: 0 20px;
  border-radius: 6px;
  margin: 4px 12px;
  width: calc(100% - 24px);
}

.admin-menu .el-menu-item:hover,
.main-menu .el-menu-item:hover {
  background: var(--el-color-primary-light-9);
}

.admin-menu .el-menu-item.is-active,
.main-menu .el-menu-item.is-active {
  background: var(--el-color-primary-light-8);
  color: var(--el-color-primary);
}

@media (max-width: 768px) {
  .app-title {
    font-size: 16px;
  }

  .user-section {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
}
</style>

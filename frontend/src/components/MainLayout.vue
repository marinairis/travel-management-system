<template>
  <div class="main-layout">
    <!-- Header fixo no topo -->
    <TheHeader />

    <div class="layout-content">
      <!-- Drawer fixo à esquerda -->
      <div class="sidebar">
        <div class="sidebar-content">
          <div class="navigation-section" v-if="authStore.isAdmin">
            <h4>Administração</h4>
            <el-menu :default-active="activeMenu" class="admin-menu" @select="handleMenuSelect">
              <el-menu-item index="users">
                <el-icon><UserFilled /></el-icon>
                <template #title>Gestão de Usuários</template>
              </el-menu-item>
              <el-menu-item index="logs">
                <el-icon><Document /></el-icon>
                <template #title>Logs de Atividades</template>
              </el-menu-item>
            </el-menu>
          </div>

          <div class="navigation-section">
            <h4>Navegação</h4>
            <el-menu :default-active="activeMenu" class="main-menu" @select="handleMenuSelect">
              <el-menu-item index="dashboard">
                <el-icon><House /></el-icon>
                <template #title>Dashboard</template>
              </el-menu-item>
            </el-menu>
          </div>
        </div>
      </div>

      <!-- Conteúdo principal -->
      <div class="main-content">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import TheHeader from '@/components/TheHeader.vue'
import { UserFilled, Document, House } from '@element-plus/icons-vue'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const activeMenu = computed(() => {
  const path = route.path
  if (path.includes('/users')) return 'users'
  if (path.includes('/logs')) return 'logs'
  if (path.includes('/dashboard') || path === '/') return 'dashboard'
  return ''
})

const handleMenuSelect = (index) => {
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
.main-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: var(--el-bg-color-page);
}

.layout-content {
  display: flex;
  flex: 1;
  height: calc(100vh - 60px); /* Altura total menos o header */
}

.sidebar {
  width: 280px;
  background-color: var(--el-bg-color);
  border-right: 1px solid var(--el-border-color-light);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 60px; /* Altura do header */
  bottom: 0;
  z-index: 100;
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px 0;
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
  text-transform: uppercase;
  letter-spacing: 0.5px;
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
  display: flex;
  align-items: center;
  gap: 12px;
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

.main-content {
  flex: 1;
  margin-left: 280px;
  overflow-y: auto;
  background-color: var(--el-bg-color-page);
}

/* Responsividade */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .sidebar.mobile-open {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
  }
}

/* Scrollbar personalizada */
.sidebar-content::-webkit-scrollbar {
  width: 4px;
}

.sidebar-content::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-content::-webkit-scrollbar-thumb {
  background: var(--el-border-color);
  border-radius: 2px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
  background: var(--el-border-color-dark);
}
</style>

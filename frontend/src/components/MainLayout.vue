<template>
  <div class="main-layout">
    <TheHeader />

    <div class="layout-content">
      <div class="sidebar" @mouseenter="onSidebarHover" @mouseleave="onSidebarLeave">
        <div class="sidebar-content">
          <div class="navigation-section" v-if="authStore.isAdmin">
            <h4 v-show="isExpanded">Administração</h4>
            <el-menu :default-active="activeMenu" class="admin-menu" @select="handleMenuSelect">
              <el-menu-item index="users">
                <el-icon><UserFilled /></el-icon>
                <template #title>
                  <span v-show="isExpanded">Gestão de Usuários</span>
                </template>
              </el-menu-item>
              <el-menu-item index="logs">
                <el-icon><Document /></el-icon>
                <template #title>
                  <span v-show="isExpanded">Logs de Atividades</span>
                </template>
              </el-menu-item>
            </el-menu>
          </div>

          <el-divider />

          <div class="navigation-section">
            <h4 v-show="isExpanded">Navegação</h4>
            <el-menu :default-active="activeMenu" class="main-menu" @select="handleMenuSelect">
              <el-menu-item index="dashboard">
                <el-icon><House /></el-icon>
                <template #title>
                  <span v-show="isExpanded">Dashboard</span>
                </template>
              </el-menu-item>
            </el-menu>
          </div>
        </div>
      </div>

      <div class="main-content">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import TheHeader from '@/components/TheHeader.vue'
import { UserFilled, Document, House } from '@element-plus/icons-vue'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const isExpanded = ref(false)

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

const onSidebarHover = () => {
  isExpanded.value = true
}

const onSidebarLeave = () => {
  isExpanded.value = false
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
  height: calc(100vh - var(--header-height));
}

.sidebar {
  width: var(--sidebar-width);
  background-color: var(--el-bg-color);
  border-right: 1px solid var(--el-border-color-light);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: var(--header-height);
  bottom: 0;
  z-index: var(--z-sidebar);
  transition: width var(--transition-normal);
  overflow: hidden;
}

.sidebar:hover {
  width: var(--sidebar-expanded-width);
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-lg) 0;
}

.navigation-section {
  margin-bottom: var(--spacing-2xl);
}

.navigation-section h4 {
  margin: 0 0 var(--spacing-md) 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--el-text-color-regular);
  padding: 0 var(--spacing-xl);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
  overflow: hidden;
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
  padding: 0 var(--spacing-xl);
  border-radius: var(--radius-md);
  margin: var(--spacing-xs) var(--spacing-md);
  width: calc(100% - var(--spacing-2xl));
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
  white-space: nowrap;
  overflow: hidden;
  transition: all var(--transition-normal);
}

/* Quando o sidebar está fechado, centralizar apenas o ícone */
.sidebar:not(:hover) .admin-menu .el-menu-item,
.sidebar:not(:hover) .main-menu .el-menu-item {
  justify-content: center;
  padding: 0 var(--spacing-sm);
  margin: var(--spacing-xs) var(--spacing-sm);
  width: calc(100% - var(--spacing-lg));
}

.sidebar:not(:hover) .admin-menu .el-menu-item .el-icon,
.sidebar:not(:hover) .main-menu .el-menu-item .el-icon {
  font-size: 20px;
  margin: 0;
}

/* Quando o sidebar está expandido, alinhar à esquerda */
.sidebar:hover .admin-menu .el-menu-item,
.sidebar:hover .main-menu .el-menu-item {
  justify-content: flex-start;
  padding: 0 var(--spacing-xl);
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
  overflow-y: auto;
  background-color: var(--el-bg-color-page);
  transition: margin-left var(--transition-normal);
}

.main-content.sidebar-expanded {
  margin-left: var(--sidebar-expanded-width);
}

/* Responsividade */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform var(--transition-normal);
  }

  .sidebar.mobile-open {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
  }
}

/* Scrollbar personalizada */
.sidebar-content {
  @extend .custom-scrollbar;
}

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

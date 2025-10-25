<template>
  <div class="main-layout">
    <TheHeader />

    <div class="layout-content">
      <!-- Overlay para mobile -->
      <div
        v-if="isMobile && sidebarOpen && authStore.isAdmin"
        class="sidebar-overlay"
        @click="closeSidebar"
      ></div>

      <!-- Sidebar - apenas para admins -->
      <div
        v-if="authStore.isAdmin"
        class="sidebar"
        :class="{
          'sidebar-open': sidebarOpen,
          'sidebar-expanded': isExpanded && !isMobile,
        }"
        @mouseenter="onSidebarHover"
        @mouseleave="onSidebarLeave"
      >
        <div class="sidebar-content">
          <div class="navigation-section" v-if="authStore.isAdmin">
            <h4 v-show="isExpanded || isMobile">{{ $t('navigation.administration') }}</h4>
            <el-menu :default-active="activeMenu" class="admin-menu" @select="handleMenuSelect">
              <el-menu-item index="users">
                <el-icon><UserFilled /></el-icon>
                <template #title>
                  <span v-show="isExpanded || isMobile">{{ $t('navigation.users') }}</span>
                </template>
              </el-menu-item>
              <el-menu-item index="logs">
                <el-icon><Document /></el-icon>
                <template #title>
                  <span v-show="isExpanded || isMobile">{{ $t('navigation.activityLogs') }}</span>
                </template>
              </el-menu-item>
            </el-menu>
          </div>

          <el-divider v-if="authStore.isAdmin" />

          <div class="navigation-section">
            <h4 v-show="isExpanded || isMobile">{{ $t('navigation.navigation') }}</h4>
            <el-menu :default-active="activeMenu" class="main-menu" @select="handleMenuSelect">
              <el-menu-item index="dashboard">
                <el-icon><House /></el-icon>
                <template #title>
                  <span v-show="isExpanded || isMobile">{{ $t('navigation.dashboard') }}</span>
                </template>
              </el-menu-item>
            </el-menu>
          </div>
        </div>
      </div>

      <div
        class="main-content"
        :class="{
          'sidebar-open': sidebarOpen && isMobile && authStore.isAdmin,
          'no-sidebar': !authStore.isAdmin,
        }"
      >
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import TheHeader from '@/components/TheHeader.vue'
import { UserFilled, Document, House } from '@element-plus/icons-vue'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const isExpanded = ref(false)
const sidebarOpen = ref(false)
const isMobile = ref(false)

const activeMenu = computed(() => {
  const path = route.path
  if (path.includes('/users')) return 'users'
  if (path.includes('/logs')) return 'logs'
  if (path.includes('/dashboard') || path === '/') return 'dashboard'
  return ''
})

const handleMenuSelect = (index) => {
  if (isMobile.value) {
    sidebarOpen.value = false
  }

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
  if (!isMobile.value) {
    isExpanded.value = true
  }
}

const onSidebarLeave = () => {
  if (!isMobile.value) {
    isExpanded.value = false
  }
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768
  if (!isMobile.value) {
    sidebarOpen.value = false
  }
}

const handleToggleSidebar = () => {
  toggleSidebar()
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  window.addEventListener('toggle-sidebar', handleToggleSidebar)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  window.removeEventListener('toggle-sidebar', handleToggleSidebar)
})
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
  position: relative;
}

.sidebar-overlay {
  position: fixed;
  top: var(--header-height);
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: var(--z-sidebar) - 1;
  backdrop-filter: blur(2px);
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
  transition: all var(--transition-normal);
  overflow: hidden;
  box-shadow: var(--shadow-lg);
}

/* Hover effect para desktop */
.sidebar:hover {
  width: var(--sidebar-expanded-width);
}

/* Sidebar expandido no desktop */
.sidebar.sidebar-expanded {
  width: var(--sidebar-expanded-width);
}

/* Sidebar aberto no mobile */
.sidebar.sidebar-open {
  transform: translateX(0);
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
.sidebar:not(:hover):not(.sidebar-expanded) .admin-menu .el-menu-item,
.sidebar:not(:hover):not(.sidebar-expanded) .main-menu .el-menu-item {
  justify-content: center;
  padding: 0 var(--spacing-sm);
  margin: var(--spacing-xs) var(--spacing-sm);
  width: calc(100% - var(--spacing-lg));
}

.sidebar:not(:hover):not(.sidebar-expanded) .admin-menu .el-menu-item .el-icon,
.sidebar:not(:hover):not(.sidebar-expanded) .main-menu .el-menu-item .el-icon {
  font-size: 20px;
  margin: 0;
}

/* Quando o sidebar está expandido, alinhar à esquerda */
.sidebar:hover .admin-menu .el-menu-item,
.sidebar:hover .main-menu .el-menu-item,
.sidebar.sidebar-expanded .admin-menu .el-menu-item,
.sidebar.sidebar-expanded .main-menu .el-menu-item {
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
  transition: all var(--transition-normal);
  margin-left: var(--sidebar-width);
}

.main-content.no-sidebar {
  margin-left: 0;
  width: 100%;
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

/* Responsividade - Mobile */
@media (max-width: 768px) {
  .sidebar {
    width: 280px;
    transform: translateX(-100%);
    transition: transform var(--transition-normal);
  }

  .sidebar.sidebar-open {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
    width: 100%;
  }
}

/* Responsividade - Tablet */
@media (min-width: 769px) and (max-width: 1024px) {
  .sidebar {
    width: var(--sidebar-width);
  }

  .main-content {
    margin-left: var(--sidebar-width);
  }
}

/* Responsividade - Desktop */
@media (min-width: 1025px) {
  .sidebar {
    width: var(--sidebar-width);
  }

  .main-content {
    margin-left: var(--sidebar-width);
  }
}
</style>

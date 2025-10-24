<template>
  <div id="app">
    <MainLayout v-if="showMainLayout" />
    <router-view v-else />
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useThemeStore } from './stores/theme'
import MainLayout from './components/MainLayout.vue'

const themeStore = useThemeStore()
const route = useRoute()

const showMainLayout = computed(() => {
  const guestPages = ['/login', '/register', '/forgot-password']
  return !guestPages.includes(route.path)
})

const updatePageTitle = () => {
  const baseTitle = 'Viagens Corporativas'
  let pageTitle = baseTitle

  switch (route.path) {
    case '/':
    case '/dashboard':
      pageTitle = `${baseTitle} | Pedidos de Viagem`
      break
    case '/logs':
      pageTitle = `${baseTitle} | Logs de Atividades`
      break
    case '/users':
      pageTitle = `${baseTitle} | Gestão de Usuários`
      break
    case '/login':
      pageTitle = `${baseTitle} | Login`
      break
    case '/register':
      pageTitle = `${baseTitle} | Cadastro`
      break
    case '/forgot-password':
      pageTitle = `${baseTitle} | Recuperar Senha`
      break
    default:
      pageTitle = baseTitle
  }

  document.title = pageTitle
}

onMounted(() => {
  themeStore.initTheme()
  updatePageTitle()
})

watch(route, () => {
  updatePageTitle()
})
</script>

<style>
#app {
  font-family:
    'Rec Mono Casual',
    Inter,
    -apple-system,
    BlinkMacSystemFont,
    'Segoe UI',
    Roboto,
    Oxygen,
    Ubuntu,
    Cantarell,
    'Fira Sans',
    'Droid Sans',
    'Helvetica Neue',
    sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html,
body {
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
}
</style>

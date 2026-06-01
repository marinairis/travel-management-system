t
<template>
  <el-config-provider :locale="localeStore.currentElementPlusLocale">
    <div id="app">
      <MainLayout v-if="showMainLayout" />
      <router-view v-else />
    </div>
  </el-config-provider>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useThemeStore } from './stores/theme'
import { useLocaleStore } from './stores/locale'
import { useI18n } from 'vue-i18n'
import MainLayout from './components/MainLayout.vue'

const themeStore = useThemeStore()
const localeStore = useLocaleStore()
const { locale } = useI18n()
const route = useRoute()

const showMainLayout = computed(() => {
  const guestPages = ['/login', '/forgot-password']
  return !guestPages.includes(route.path) && !route.path.startsWith('/invitation/')
})

watch(
  () => localeStore.currentLocale,
  (newLocale) => {
    locale.value = newLocale
  },
)

onMounted(() => {
  themeStore.initTheme()
  locale.value = localeStore.currentLocale
})
</script>

<style>
/* Reset global mínimo - estilos completos estão em variables.css */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
</style>

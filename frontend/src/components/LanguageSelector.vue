<template>
  <el-select
    v-model="currentLocale"
    size="small"
    style="width: 100px"
    @change="handleChange"
  >
    <el-option value="pt-BR" label="🇧🇷 PT" />
    <el-option value="en" label="🇺🇸 EN" />
    <el-option value="es" label="🇪🇸 ES" />
  </el-select>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocaleStore } from '@/stores/locale'

const { locale } = useI18n()
const localeStore = useLocaleStore()

const currentLocale = computed({
  get: () => localeStore.currentLocale,
  set: (val) => localeStore.setLocale(val),
})

const handleChange = (val) => {
  locale.value = val
}
</script>

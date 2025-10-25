<template>
  <el-select v-model="currentLocale" @change="handleLocaleChange" size="small" style="width: 120px">
    <el-option
      v-for="locale in availableLocales"
      :key="locale.value"
      :label="locale.label"
      :value="locale.value"
    />
  </el-select>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()

const currentLocale = ref('pt-BR')

const availableLocales = [
  { label: '🇧🇷 Português', value: 'pt-BR' },
  { label: '🇺🇸 English', value: 'en-US' },
]

const handleLocaleChange = (newLocale) => {
  locale.value = newLocale
  localStorage.setItem('locale', newLocale)
  currentLocale.value = newLocale
}

onMounted(() => {
  const savedLocale = localStorage.getItem('locale') || 'pt-BR'
  currentLocale.value = savedLocale
  locale.value = savedLocale
})
</script>

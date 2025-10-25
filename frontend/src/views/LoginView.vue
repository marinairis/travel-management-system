<template>
  <div class="auth-container">
    <div class="language-selector-container">
      <LanguageSelector />
    </div>
    <el-card class="auth-card">
      <template #header>
        <div class="auth-card-header">
          <el-icon :size="32" color="#409EFF">
            <MapLocation />
          </el-icon>
          <h2>{{ $t('auth.title') }}</h2>
        </div>
      </template>

      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        label-position="top"
        @submit.prevent="handleLogin"
      >
        <el-form-item :label="$t('auth.email')" prop="email">
          <el-input
            v-model="formData.email"
            :placeholder="$t('auth.emailPlaceholder')"
            :prefix-icon="Message"
          />
        </el-form-item>

        <el-form-item :label="$t('auth.password')" prop="password">
          <el-input
            v-model="formData.password"
            type="password"
            :placeholder="$t('auth.passwordPlaceholder')"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" class="auth-button" :loading="loading" @click="handleLogin">
            {{ $t('auth.login') }}
          </el-button>
        </el-form-item>

        <div class="auth-form-links">
          <router-link to="/register">{{ $t('auth.register') }}</router-link>
          <router-link to="/forgot-password">{{ $t('auth.forgotPassword') }}</router-link>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'
import LanguageSelector from '@/components/LanguageSelector.vue'
import { MapLocation, Message, Lock } from '@element-plus/icons-vue'

const { t } = useI18n()

const authStore = useAuthStore()

const formRef = ref(null)
const loading = ref(false)

const formData = reactive({
  email: '',
  password: '',
})

const rules = {
  email: [
    { required: true, message: t('auth.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('auth.emailInvalid'), trigger: 'blur' },
  ],
  password: [{ required: true, message: t('auth.passwordRequired'), trigger: 'blur' }],
}

const handleLogin = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true
      try {
        await authStore.login(formData)
      } catch (error) {
        console.error(t('auth.loginError'), error)
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

<style scoped>
.language-selector-container {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 1000;
}
</style>

<template>
  <div class="auth-container">
    <div class="language-selector-container">
      <LanguageSelector />
    </div>

    <div class="auth-cards-wrapper">
      <el-card class="auth-card">
        <template #header>
          <div class="auth-card-header">
            <el-icon :size="36" color="voa-brand-icon">
              <Place />
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
            <router-link to="/forgot-password">{{ $t('auth.forgotPassword') }}</router-link>
          </div>
        </el-form>
      </el-card>

      <el-card class="auth-card demo-accounts-card">
        <div class="demo-accounts-title">{{ $t('demo.accountsTitle') }}</div>
        <div
          v-for="user in DEMO_USERS"
          :key="user.email"
          class="demo-account-row"
          @click="fillCredentials(user)"
        >
          <el-tag :type="getRoleTagType(user.role)" size="small" class="demo-account-role">{{
            getRoleLabel(user.role)
          }}</el-tag>
          <span class="demo-account-email">{{ user.email }}</span>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'
import { Message, Lock } from '@element-plus/icons-vue'
import LanguageSelector from '@/components/LanguageSelector.vue'
import { useRole } from '@/composables/useRole'
import { DEMO_USERS } from '@/constants/demoUsers'

const { t } = useI18n()
const authStore = useAuthStore()
const { getRoleLabel, getRoleTagType } = useRole()

const formRef = ref(null)
const loading = ref(false)

const formData = reactive({
  email: '',
  password: '',
})

const fillCredentials = (user) => {
  formData.email = user.email
  formData.password = user.password
}

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

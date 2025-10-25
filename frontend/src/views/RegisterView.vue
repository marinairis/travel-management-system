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
          <h2>{{ $t('auth.register') }}</h2>
        </div>
      </template>

      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        label-position="top"
        @submit.prevent="handleRegister"
      >
        <el-form-item :label="$t('users.name')" prop="name">
          <el-input
            v-model="formData.name"
            :placeholder="$t('auth.namePlaceholder')"
            :prefix-icon="User"
          />
        </el-form-item>

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

        <el-form-item :label="$t('auth.confirmPassword')" prop="password_confirmation">
          <el-input
            v-model="formData.password_confirmation"
            type="password"
            :placeholder="$t('auth.confirmPasswordPlaceholder')"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" class="auth-button" :loading="loading" @click="handleRegister">
            {{ $t('auth.register') }}
          </el-button>
        </el-form-item>

        <div class="auth-form-links center">
          <router-link to="/login">{{ $t('auth.alreadyHaveAccount') }}</router-link>
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
import { MapLocation, Message, Lock, User } from '@element-plus/icons-vue'

const { t } = useI18n()

const authStore = useAuthStore()

const formRef = ref(null)
const loading = ref(false)

const formData = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const validatePasswordMatch = (rule, value, callback) => {
  if (value !== formData.password) {
    callback(new Error(t('auth.passwordsDoNotMatch')))
  } else {
    callback()
  }
}

const rules = {
  name: [{ required: true, message: t('users.nameRequired'), trigger: 'blur' }],
  email: [
    { required: true, message: t('auth.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('auth.emailInvalid'), trigger: 'blur' },
  ],
  password: [
    { required: true, message: t('auth.passwordRequired'), trigger: 'blur' },
    { min: 6, message: t('auth.passwordMinLength'), trigger: 'blur' },
  ],
  password_confirmation: [
    { required: true, message: t('auth.confirmPasswordRequired'), trigger: 'blur' },
    { validator: validatePasswordMatch, trigger: 'blur' },
  ],
}

const handleRegister = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true
      try {
        await authStore.register(formData)
      } catch (error) {
        console.error(t('auth.registerError'), error)
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

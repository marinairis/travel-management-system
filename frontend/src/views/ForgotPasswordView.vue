<template>
  <div class="auth-container">
    <div class="language-selector-container">
      <LanguageSelector />
    </div>
    <el-card class="auth-card">
      <template #header>
        <div class="auth-card-header">
          <el-icon :size="32" class="auth-icon">
            <MapLocation />
          </el-icon>
          <h2>{{ $t('auth.forgotPassword') }}</h2>
        </div>
      </template>

      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        label-position="top"
        @submit.prevent="handleSubmit"
      >
        <el-alert
          v-if="!success"
          :title="$t('auth.enterEmail')"
          :description="$t('auth.emailSentDescription')"
          type="info"
          :closable="false"
          style="margin-bottom: 20px"
        />

        <el-alert
          v-if="success"
          :title="$t('auth.emailSent')"
          :description="$t('auth.checkInbox')"
          type="success"
          :closable="false"
          style="margin-bottom: 20px"
        />

        <el-form-item :label="$t('auth.email')" prop="email">
          <el-input
            v-model="formData.email"
            :placeholder="$t('auth.emailPlaceholder')"
            :prefix-icon="Message"
            :disabled="success"
          />
        </el-form-item>

        <el-form-item>
          <el-button
            v-if="!success"
            type="primary"
            class="auth-button"
            :loading="loading"
            @click="handleSubmit"
          >
            {{ $t('auth.sendLink') }}
          </el-button>
          <el-button v-else class="auth-button" @click="$router.push('/login')">
            {{ $t('auth.backToLogin') }}
          </el-button>
        </el-form-item>

        <div class="auth-form-links center">
          <router-link to="/login">{{ $t('auth.backToLogin') }}</router-link>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { usePasswordResetStore } from '@/stores/passwordReset'
import { useI18n } from 'vue-i18n'
import { MapLocation, Message } from '@element-plus/icons-vue'
import LanguageSelector from '@/components/LanguageSelector.vue'

const { t } = useI18n()

const passwordResetStore = usePasswordResetStore()

const formRef = ref(null)
const loading = ref(false)
const success = ref(false)

const formData = reactive({
  email: '',
})

const rules = {
  email: [
    { required: true, message: t('auth.emailRequired'), trigger: 'blur' },
    { type: 'email', message: t('auth.emailInvalid'), trigger: 'blur' },
  ],
}

const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true
      try {
        await passwordResetStore.forgotPassword(formData.email)
        success.value = true
      } catch (error) {
        console.error(t('auth.emailSendError'), error)
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

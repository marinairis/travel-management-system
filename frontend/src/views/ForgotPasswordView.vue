<template>
  <div class="forgot-container">
    <div class="language-selector-container">
      <LanguageSelector />
    </div>
    <el-card class="forgot-card">
      <template #header>
        <div class="card-header">
          <el-icon :size="32" color="#409EFF">
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
            style="width: 100%"
            :loading="loading"
            @click="handleSubmit"
          >
            {{ $t('auth.sendLink') }}
          </el-button>
          <el-button v-else style="width: 100%" @click="$router.push('/login')">
            {{ $t('auth.backToLogin') }}
          </el-button>
        </el-form-item>

        <div class="form-links">
          <router-link to="/login">{{ $t('auth.backToLogin') }}</router-link>
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
import { MapLocation, Message } from '@element-plus/icons-vue'

const { t } = useI18n()

const authStore = useAuthStore()

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
        await authStore.forgotPassword(formData.email)
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

<style scoped>
.forgot-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  position: relative;
}

.language-selector-container {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 1000;
}

.forgot-card {
  width: 100%;
  max-width: 400px;
}

.card-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.card-header h2 {
  margin: 0;
  font-size: 24px;
}

.form-links {
  display: flex;
  justify-content: center;
  margin-top: 16px;
}

.form-links a {
  color: var(--el-color-primary);
  text-decoration: none;
  font-size: 14px;
}

.form-links a:hover {
  text-decoration: underline;
}
</style>

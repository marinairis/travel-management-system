<template>
  <div class="auth-container">
    <div class="language-selector-container">
      <LanguageSelector />
    </div>

    <el-card class="auth-card">
      <template #header>
        <div class="auth-card-header">
          <el-icon :size="36" :color="'#ACC8A2'">
            <MapLocation />
          </el-icon>
          <h2>{{ $t('auth.acceptInvitation') }}</h2>
        </div>
      </template>

      <div v-if="loading" class="invite-loading">
        <el-icon class="is-loading" :size="32"><Loading /></el-icon>
      </div>

      <div v-else-if="error" class="invite-error">
        <el-result icon="error" :title="$t('auth.invitationExpired')">
          <template #extra>
            <router-link to="/login">
              <el-button type="primary">{{ $t('auth.backToLogin') }}</el-button>
            </router-link>
          </template>
        </el-result>
      </div>

      <template v-else>
        <p class="invite-welcome">{{ $t('auth.invitationWelcome') }}</p>
        <p class="invite-email">{{ invitation.email }}</p>

        <el-form
          ref="formRef"
          :model="formData"
          :rules="rules"
          label-position="top"
          @submit.prevent="handleAccept"
        >
          <el-form-item :label="$t('auth.name')" prop="name">
            <el-input
              v-model="formData.name"
              :placeholder="$t('auth.namePlaceholder')"
              :prefix-icon="User"
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
            <el-button
              type="primary"
              class="auth-button"
              :loading="submitting"
              @click="handleAccept"
            >
              {{ $t('auth.acceptInvitation') }}
            </el-button>
          </el-form-item>

          <div class="auth-form-links center">
            <router-link to="/login">{{ $t('auth.backToLogin') }}</router-link>
          </div>
        </el-form>
      </template>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useInvitationStore } from '@/stores/invitation'
import { useI18n } from 'vue-i18n'
import { MapLocation, User, Lock, Loading } from '@element-plus/icons-vue'
import LanguageSelector from '@/components/LanguageSelector.vue'
import { usePasswordValidation } from '@/composables/usePasswordValidation'

const { t } = useI18n()
const route = useRoute()
const invitationStore = useInvitationStore()
const { passwordComplexityRule, passwordMatchRule } = usePasswordValidation()

const formRef = ref(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref(false)
const invitation = ref({ email: '', role: '' })

const formData = reactive({
  name: '',
  password: '',
  password_confirmation: '',
})

const rules = {
  name: [{ required: true, message: t('auth.nameRequired'), trigger: 'blur' }],
  password: [
    { required: true, message: t('auth.passwordRequired'), trigger: 'blur' },
    { min: 8, message: t('auth.passwordMinLength'), trigger: 'blur' },
    passwordComplexityRule,
  ],
  password_confirmation: [
    { required: true, message: t('auth.confirmPasswordRequired'), trigger: 'blur' },
    passwordMatchRule(formData),
  ],
}

onMounted(async () => {
  try {
    const data = await invitationStore.fetchInvitation(route.params.token)
    if (data) {
      invitation.value = data
    } else {
      error.value = true
    }
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
})

const handleAccept = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        const accepted = await invitationStore.acceptInvitation(route.params.token, formData)
        if (!accepted) error.value = true
      } catch {
        error.value = true
      } finally {
        submitting.value = false
      }
    }
  })
}
</script>

<style scoped>
.invite-loading {
  display: flex;
  justify-content: center;
  padding: 40px 0;
}

.invite-welcome {
  text-align: center;
  color: var(--el-text-color-secondary);
  font-size: 14px;
  margin: 0 0 4px;
}

.invite-email {
  text-align: center;
  font-weight: 600;
  color: var(--color-olive);
  margin: 0 0 20px;
}
</style>

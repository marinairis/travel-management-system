<template>
  <div class="forgot-container">
    <el-card class="forgot-card">
      <template #header>
        <div class="card-header">
          <el-icon :size="32" color="#409EFF">
            <MapLocation />
          </el-icon>
          <h2>Recuperar Senha</h2>
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
          title="Informe seu email"
          description="Enviaremos um link para redefinir sua senha"
          type="info"
          :closable="false"
          style="margin-bottom: 20px"
        />

        <el-alert
          v-if="success"
          title="Email enviado!"
          description="Verifique sua caixa de entrada"
          type="success"
          :closable="false"
          style="margin-bottom: 20px"
        />

        <el-form-item label="Email" prop="email">
          <el-input
            v-model="formData.email"
            placeholder="seu@email.com"
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
            Enviar Link
          </el-button>
          <el-button v-else style="width: 100%" @click="$router.push('/login')">
            Voltar para Login
          </el-button>
        </el-form-item>

        <div class="form-links">
          <router-link to="/login">Voltar para login</router-link>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { MapLocation, Message } from '@element-plus/icons-vue'

const authStore = useAuthStore()

const formRef = ref(null)
const loading = ref(false)
const success = ref(false)

const formData = reactive({
  email: '',
})

const rules = {
  email: [
    { required: true, message: 'Email é obrigatório', trigger: 'blur' },
    { type: 'email', message: 'Email inválido', trigger: 'blur' },
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
        console.error('Erro ao enviar email:', error)
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

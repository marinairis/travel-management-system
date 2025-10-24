<template>
  <div class="register-container">
    <el-card class="register-card">
      <template #header>
        <div class="card-header">
          <el-icon :size="32" color="#409EFF">
            <Van />
          </el-icon>
          <h2>Criar Conta</h2>
        </div>
      </template>

      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        label-position="top"
        @submit.prevent="handleRegister"
      >
        <el-form-item label="Nome" prop="name">
          <el-input v-model="formData.name" placeholder="Seu nome completo" :prefix-icon="User" />
        </el-form-item>

        <el-form-item label="Email" prop="email">
          <el-input v-model="formData.email" placeholder="seu@email.com" :prefix-icon="Message" />
        </el-form-item>

        <el-form-item label="Senha" prop="password">
          <el-input
            v-model="formData.password"
            type="password"
            placeholder="Mínimo 6 caracteres"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>

        <el-form-item label="Confirmar Senha" prop="password_confirmation">
          <el-input
            v-model="formData.password_confirmation"
            type="password"
            placeholder="Digite a senha novamente"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" style="width: 100%" :loading="loading" @click="handleRegister">
            Criar Conta
          </el-button>
        </el-form-item>

        <div class="form-links">
          <router-link to="/login">Já tem uma conta? Faça login</router-link>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { Van, Message, Lock, User } from '@element-plus/icons-vue'

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
    callback(new Error('As senhas não coincidem'))
  } else {
    callback()
  }
}

const rules = {
  name: [{ required: true, message: 'Nome é obrigatório', trigger: 'blur' }],
  email: [
    { required: true, message: 'Email é obrigatório', trigger: 'blur' },
    { type: 'email', message: 'Email inválido', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'Senha é obrigatória', trigger: 'blur' },
    { min: 6, message: 'A senha deve ter no mínimo 6 caracteres', trigger: 'blur' },
  ],
  password_confirmation: [
    { required: true, message: 'Confirmação de senha é obrigatória', trigger: 'blur' },
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
        console.error('Erro no registro:', error)
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.register-card {
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

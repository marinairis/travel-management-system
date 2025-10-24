<template>
  <div class="auth-container">
    <el-card class="auth-card">
      <template #header>
        <div class="auth-card-header">
          <el-icon :size="32" color="#409EFF">
            <MapLocation />
          </el-icon>
          <h2>Viagens Corporativas</h2>
        </div>
      </template>

      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        label-position="top"
        @submit.prevent="handleLogin"
      >
        <el-form-item label="Email" prop="email">
          <el-input v-model="formData.email" placeholder="seu@email.com" :prefix-icon="Message" />
        </el-form-item>

        <el-form-item label="Senha" prop="password">
          <el-input
            v-model="formData.password"
            type="password"
            placeholder="Digite sua senha"
            :prefix-icon="Lock"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" class="auth-button" :loading="loading" @click="handleLogin">
            Entrar
          </el-button>
        </el-form-item>

        <div class="auth-form-links">
          <router-link to="/register">Criar conta</router-link>
          <router-link to="/forgot-password">Esqueci minha senha</router-link>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { MapLocation, Message, Lock } from '@element-plus/icons-vue'

const authStore = useAuthStore()

const formRef = ref(null)
const loading = ref(false)

const formData = reactive({
  email: '',
  password: '',
})

const rules = {
  email: [
    { required: true, message: 'Email é obrigatório', trigger: 'blur' },
    { type: 'email', message: 'Email inválido', trigger: 'blur' },
  ],
  password: [{ required: true, message: 'Senha é obrigatória', trigger: 'blur' }],
}

const handleLogin = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true
      try {
        await authStore.login(formData)
      } catch (error) {
        console.error('Erro no login:', error)
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

<style scoped></style>

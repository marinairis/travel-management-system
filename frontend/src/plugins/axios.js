import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { ElMessage } from 'element-plus'
import router from '@/router'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  },
)

api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response) {
      switch (error.response.status) {
        case 401:
          const authStore = useAuthStore()
          authStore.logout()
          router.push('/login')
          ElMessage.error('Sessão expirada. Faça login novamente.')
          break
        case 403:
          ElMessage.error('Você não tem permissão para realizar esta ação.')
          break
        case 404:
          ElMessage.error('Recurso não encontrado.')
          break
        case 422:
          if (error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            errors.forEach((err) => ElMessage.error(err))
          }
          break
        case 500:
          ElMessage.error('Erro interno do servidor.')
          break
        default:
          ElMessage.error(error.response.data.message || 'Erro desconhecido.')
      }
    } else if (error.request) {
      ElMessage.error('Erro de conexão com o servidor.')
    }
    return Promise.reject(error)
  },
)

export default api

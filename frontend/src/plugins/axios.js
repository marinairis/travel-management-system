import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useLocaleStore } from '@/stores/locale'
import { ElMessage } from 'element-plus'
import router from '@/router'

const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/api`,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    const localeStore = useLocaleStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    config.headers['Accept-Language'] = localeStore.currentLocale ?? 'pt-BR'
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
          if (authStore.token) {
            authStore.logout()
            ElMessage.error('Sessão expirada. Faça login novamente.')
          }
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

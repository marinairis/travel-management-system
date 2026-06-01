import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useLocaleStore } from '@/stores/locale'
import { ElMessage } from 'element-plus'
import router from '@/router'
import { i18n } from '@/i18n'

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
      const t = (key) => i18n.global.t(key)
      switch (error.response.status) {
        case 401:
          const authStore = useAuthStore()
          if (authStore.token) {
            authStore.logout()
            ElMessage.error(t('errors.sessionExpired'))
          }
          break
        case 403:
          ElMessage.error(t('errors.forbidden'))
          break
        case 404:
          ElMessage.error(t('errors.notFound'))
          break
        case 422:
          if (error.response.data.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            errors.forEach((err) => ElMessage.error(err))
          }
          break
        case 500:
          ElMessage.error(t('errors.serverError'))
          break
        default:
          ElMessage.error(error.response.data.message || t('errors.unknownError'))
      }
    } else if (error.request) {
      ElMessage.error(i18n.global.t('errors.connectionError'))
    }
    return Promise.reject(error)
  },
)

export default api

import axios from 'axios'
import router from '../router'
import i18n from './i18n'

const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request Interceptor: Attach Token and Accept-Language dynamically
apiClient.interceptors.request.use(
  config => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // Pass the active vue-i18n locale to the backend
    const locale = i18n.global.locale.value || 'vi'
    config.headers['Accept-Language'] = locale

    return config
  },
  error => {
    return Promise.reject(error)
  },
)

// Response Interceptor: Handle auth errors
apiClient.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // Clear storage
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

      // Redirect to login
      router.push('/login')
    }
    return Promise.reject(error)
  },
)

export default apiClient

import { defineStore } from 'pinia'
import apiClient from '../plugins/axios'
import i18n from '../plugins/i18n'

interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'user'
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || '',
    user: localStorage.getItem('auth_user')
      ? JSON.parse(localStorage.getItem('auth_user') as string) as User
      : null as User | null,
    loading: false,
    error: '',
    currentLocale: localStorage.getItem('app_locale') || 'vi',
  }),

  getters: {
    isAuthenticated: state => !!state.token,
    isAdmin: state => state.user?.role === 'admin',
  },

  actions: {
    setLocale (locale: 'vi' | 'en') {
      this.currentLocale = locale
      localStorage.setItem('app_locale', locale)
      i18n.global.locale.value = locale
    },

    initLocale () {
      const locale = (localStorage.getItem('app_locale') || 'vi') as 'vi' | 'en'
      this.setLocale(locale)
    },

    async login (credentials: { email: '', password: '' }) {
      this.loading = true
      this.error = ''
      try {
        const response = await apiClient.post('/login', credentials)

        // Match the unified JSON response structure: response.data.data
        const responseData = response.data.data

        this.token = responseData.access_token
        this.user = responseData.user

        localStorage.setItem('auth_token', this.token)
        localStorage.setItem('auth_user', JSON.stringify(this.user))

        return true
      } catch (error: any) {
        this.error = error.response && error.response.data && error.response.data.message ? error.response.data.message : 'Đã xảy ra lỗi kết nối.'
        return false
      } finally {
        this.loading = false
      }
    },

    async logout () {
      try {
        await apiClient.post('/logout')
      } catch {
        // Suppress logout API failure
      } finally {
        this.token = ''
        this.user = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },
  },
})

import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/lib/api'

export interface AuthUser {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => Boolean(token.value && user.value))

  async function login(email: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/login', { email, password })
      token.value = data.token
      user.value = data.user
      localStorage.setItem('auth_token', data.token)
    } catch (err: unknown) {
      error.value = extractError(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const { data } = await api.get('/user')
      user.value = data.user
    } catch {
      logout()
    }
  }

  async function logout() {
    try {
      if (token.value) await api.post('/logout')
    } catch {
      // ignore network errors on logout
    }
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
  }

  function extractError(err: unknown): string {
    if (axiosIsError(err) && err.response?.data?.message) {
      return err.response.data.message
    }
    if (axiosIsError(err) && err.response?.data?.errors) {
      const first = Object.values(err.response.data.errors)[0]
      return Array.isArray(first) ? (first[0] ?? 'Login failed') : 'Login failed'
    }
    return 'Unable to sign in. Please try again.'
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    login,
    fetchUser,
    logout,
  }
})

function axiosIsError(err: unknown): err is { response?: { data?: { message?: string; errors?: Record<string, string[]> } } } {
  return typeof err === 'object' && err !== null && 'response' in err
}

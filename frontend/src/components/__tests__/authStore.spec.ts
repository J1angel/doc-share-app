import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import api from '@/lib/api'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/lib/api', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
  },
}))

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('stores token and user on successful login', async () => {
    vi.mocked(api.post).mockResolvedValue({
      data: {
        token: 'test-token',
        user: { id: 1, name: 'Alice', email: 'alice@example.com' },
      },
    })

    const auth = useAuthStore()
    await auth.login('alice@example.com', 'password')

    expect(auth.isAuthenticated).toBe(true)
    expect(localStorage.getItem('auth_token')).toBe('test-token')
    expect(auth.user?.email).toBe('alice@example.com')
  })
})

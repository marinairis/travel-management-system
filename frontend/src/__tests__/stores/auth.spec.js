import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/plugins/axios', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
  },
}))

vi.mock('@/router', () => ({
  default: { push: vi.fn() },
}))

vi.mock('element-plus', () => ({
  ElMessage: { success: vi.fn(), error: vi.fn() },
}))

import api from '@/plugins/axios'
import router from '@/router'

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('estado inicial está vazio', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(store.isAdmin).toBe(false)
  })

  it('login com sucesso armazena token e usuário', async () => {
    const store = useAuthStore()
    const fakeUser = { id: 1, name: 'Maria', is_admin: false }
    api.post.mockResolvedValueOnce({
      data: { success: true, data: { token: 'jwt-token', user: fakeUser } },
    })

    await store.login({ email: 'maria@test.com', password: '123456' })

    expect(store.token).toBe('jwt-token')
    expect(store.user).toEqual(fakeUser)
    expect(store.isAuthenticated).toBe(true)
    expect(router.push).toHaveBeenCalledWith('/')
  })

  it('isAdmin retorna true para usuário admin', async () => {
    const store = useAuthStore()
    api.post.mockResolvedValueOnce({
      data: { success: true, data: { token: 'tok', user: { id: 2, name: 'Admin', is_admin: true } } },
    })

    await store.login({ email: 'admin@test.com', password: '123456' })

    expect(store.isAdmin).toBe(true)
  })

  it('logout limpa estado e redireciona para /login', async () => {
    const store = useAuthStore()
    store.token = 'tok'
    store.user = { id: 1 }
    api.post.mockResolvedValueOnce({})

    await store.logout()

    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(router.push).toHaveBeenCalledWith('/login')
  })

  it('logout funciona mesmo se a API falhar', async () => {
    const store = useAuthStore()
    store.token = 'tok'
    api.post.mockRejectedValueOnce(new Error('network error'))

    await store.logout()

    expect(store.token).toBeNull()
    expect(router.push).toHaveBeenCalledWith('/login')
  })

  it('fetchUser atualiza dados do usuário', async () => {
    const store = useAuthStore()
    const fakeUser = { id: 1, name: 'Maria', is_admin: false }
    api.get.mockResolvedValueOnce({ data: { success: true, data: fakeUser } })

    await store.fetchUser()

    expect(store.user).toEqual(fakeUser)
  })

  it('register armazena token e redireciona', async () => {
    const store = useAuthStore()
    const fakeUser = { id: 3, name: 'João', is_admin: false }
    api.post.mockResolvedValueOnce({
      data: { success: true, data: { token: 'new-token', user: fakeUser } },
    })

    await store.register({ name: 'João', email: 'joao@test.com', password: '123456', password_confirmation: '123456' })

    expect(store.token).toBe('new-token')
    expect(store.user).toEqual(fakeUser)
    expect(router.push).toHaveBeenCalledWith('/')
  })
})

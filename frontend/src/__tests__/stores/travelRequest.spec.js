import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useTravelRequestStore } from '@/stores/travelRequest'

vi.mock('@/plugins/axios', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}))

vi.mock('element-plus', () => ({
  ElMessage: { success: vi.fn(), error: vi.fn() },
}))

import api from '@/plugins/axios'

const mockRequests = [
  { id: 1, requester_name: 'Maria', destination: 'São Paulo', status: 'requested' },
  { id: 2, requester_name: 'João', destination: 'Rio de Janeiro', status: 'approved' },
]

describe('travelRequest store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('estado inicial está vazio', () => {
    const store = useTravelRequestStore()
    expect(store.travelRequests).toEqual([])
    expect(store.isLoading).toBe(false)
  })

  it('fetchTravelRequests popula a lista', async () => {
    const store = useTravelRequestStore()
    api.get.mockResolvedValueOnce({ data: { success: true, data: mockRequests } })

    await store.fetchTravelRequests()

    expect(store.travelRequests).toEqual(mockRequests)
    expect(store.isLoading).toBe(false)
  })

  it('fetchTravelRequests envia filtro de status na URL', async () => {
    const store = useTravelRequestStore()
    api.get.mockResolvedValueOnce({ data: { success: true, data: [] } })

    await store.fetchTravelRequests({ status: 'approved' })

    expect(api.get).toHaveBeenCalledWith(expect.stringContaining('status=approved'))
  })

  it('fetchTravelRequests envia filtro de destino na URL', async () => {
    const store = useTravelRequestStore()
    api.get.mockResolvedValueOnce({ data: { success: true, data: [] } })

    await store.fetchTravelRequests({ destination: 'São Paulo' })

    expect(api.get).toHaveBeenCalledWith(expect.stringContaining('destination=S%C3%A3o+Paulo'))
  })

  it('createTravelRequest retorna true e recarrega lista após sucesso', async () => {
    const store = useTravelRequestStore()
    api.post.mockResolvedValueOnce({ data: { success: true, message: 'Criado!' } })
    api.get.mockResolvedValueOnce({ data: { success: true, data: mockRequests } })

    const result = await store.createTravelRequest({ requester_name: 'Maria', destination: 'SP' })

    expect(result).toBe(true)
    expect(api.post).toHaveBeenCalledWith('/travel-requests', expect.any(Object))
  })

  it('createTravelRequest retorna false em caso de erro', async () => {
    const store = useTravelRequestStore()
    api.post.mockRejectedValueOnce(new Error('API error'))

    const result = await store.createTravelRequest({})

    expect(result).toBe(false)
  })

  it('updateStatus chama o endpoint correto', async () => {
    const store = useTravelRequestStore()
    api.patch.mockResolvedValueOnce({ data: { success: true, message: 'Atualizado!' } })
    api.get.mockResolvedValueOnce({ data: { success: true, data: [] } })

    const result = await store.updateStatus(1, 'approved')

    expect(result).toBe(true)
    expect(api.patch).toHaveBeenCalledWith('/travel-requests/1/status', { status: 'approved' })
  })

  it('cancelTravelRequest chama o endpoint de cancelamento', async () => {
    const store = useTravelRequestStore()
    api.patch.mockResolvedValueOnce({ data: { success: true, message: 'Cancelado!' } })
    api.get.mockResolvedValueOnce({ data: { success: true, data: [] } })

    const result = await store.cancelTravelRequest(2)

    expect(result).toBe(true)
    expect(api.patch).toHaveBeenCalledWith('/travel-requests/2/cancel', { reason: '' })
  })

  it('deleteTravelRequest chama o endpoint de exclusão', async () => {
    const store = useTravelRequestStore()
    api.delete.mockResolvedValueOnce({ data: { success: true, message: 'Excluído!' } })
    api.get.mockResolvedValueOnce({ data: { success: true, data: [] } })

    const result = await store.deleteTravelRequest(3)

    expect(result).toBe(true)
    expect(api.delete).toHaveBeenCalledWith('/travel-requests/3')
  })

  it('loading é true durante a requisição e false ao finalizar', async () => {
    const store = useTravelRequestStore()
    let resolveGet
    api.get.mockReturnValueOnce(
      new Promise((res) => {
        resolveGet = res
      }),
    )

    const fetchPromise = store.fetchTravelRequests()
    expect(store.isLoading).toBe(true)

    resolveGet({ data: { success: true, data: [] } })
    await fetchPromise
    expect(store.isLoading).toBe(false)
  })
})

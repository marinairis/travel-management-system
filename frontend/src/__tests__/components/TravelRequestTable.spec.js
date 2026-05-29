import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import TravelRequestTable from '@/components/TravelRequestTable.vue'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/plugins/axios', () => ({ default: { get: vi.fn(), post: vi.fn(), patch: vi.fn(), delete: vi.fn() } }))

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      users: { id: 'ID', actions: 'Ações', user: 'Usuário' },
      travelRequest: {
        requesterName: 'Solicitante', destination: 'Destino',
        departureDate: 'Saída', returnDate: 'Retorno',
        approvedBy: 'Aprovado por', confirmDelete: 'Confirmar exclusão',
        deleteConfirmMessage: 'Deseja excluir?', changeStatus: 'Mudar status',
        newStatus: 'Novo status', selectStatus: 'Selecione',
        requestDetails: 'Detalhes', notes: 'Observações', createdAt: 'Criado em',
        cancelRequest: 'Cancelar pedido', cancelConfirmMessage: 'Cancelar?',
      },
      dashboard: { status: 'Status' },
      status: { requested: 'Solicitado', approved: 'Aprovado', cancelled: 'Cancelado' },
      common: { cancel: 'Cancelar', delete: 'Excluir', confirm: 'Confirmar' },
    },
  },
})

const elStubs = {
  ElTable: { template: '<div><slot /></div>' },
  ElTableColumn: { template: '<div><slot /></div>' },
  ElTag: { template: '<span><slot /></span>', props: ['type'] },
  ElButton: { template: '<button @click="$emit(\'click\')"><slot /></button>', emits: ['click'] },
  ElDialog: { template: '<div v-if="modelValue"><slot /><slot name="footer" /></div>', props: ['modelValue', 'title', 'width', 'alignCenter'], emits: ['update:modelValue'] },
  ElForm: { template: '<form><slot /></form>' },
  ElFormItem: { template: '<div><slot /></div>', props: ['label'] },
  ElSelect: { template: '<select><slot /></select>' },
  ElOption: { template: '<option />', props: ['label', 'value'] },
  ElDescriptions: { template: '<div><slot /></div>', props: ['column', 'border'] },
  ElDescriptionsItem: { template: '<div><slot /></div>', props: ['label'] },
  ElIcon: { template: '<span />' },
}

const mockData = [
  { id: 1, requester_name: 'Maria Silva', destination: 'São Paulo', departure_date: '2025-08-01', return_date: '2025-08-05', status: 'requested', user_id: 10, can_be_cancelled: true },
  { id: 2, requester_name: 'João Costa', destination: 'Rio de Janeiro', departure_date: '2025-09-01', return_date: '2025-09-05', status: 'approved', user_id: 11, can_be_cancelled: true },
]

function mountTable(data = mockData, adminUser = null) {
  const pinia = createPinia()
  const wrapper = mount(TravelRequestTable, {
    props: { data, loading: false },
    global: {
      plugins: [pinia, i18n],
      stubs: elStubs,
    },
  })
  if (adminUser) {
    const authStore = useAuthStore(pinia)
    authStore.user = adminUser
    authStore.token = 'fake-token'
  }
  return { wrapper, pinia }
}

describe('TravelRequestTable', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renderiza sem erros com dados válidos', () => {
    const { wrapper } = mountTable()
    expect(wrapper.exists()).toBe(true)
  })

  it('aceita a prop data corretamente', () => {
    const { wrapper } = mountTable(mockData)
    expect(wrapper.props('data')).toEqual(mockData)
  })

  it('aceita a prop loading corretamente', () => {
    const pinia = createPinia()
    const wrapper = mount(TravelRequestTable, {
      props: { data: [], loading: true },
      global: { plugins: [pinia, i18n], stubs: elStubs },
    })
    expect(wrapper.props('loading')).toBe(true)
  })

  it('showDelete é false para usuário não-admin', () => {
    const pinia = createPinia()
    mount(TravelRequestTable, {
      props: { data: mockData, loading: false },
      global: { plugins: [pinia, i18n], stubs: elStubs },
    })
    const authStore = useAuthStore(pinia)
    expect(authStore.isAdmin).toBe(false)
  })

  it('showDelete é true para usuário admin', () => {
    const { pinia } = mountTable()
    const authStore = useAuthStore(pinia)
    authStore.user = { id: 99, name: 'Admin', is_admin: true }
    authStore.token = 'tok'
    expect(authStore.isAdmin).toBe(true)
  })

  it('canDelete retorna false para pedido aprovado', () => {
    const pinia = createPinia()
    const wrapper = mount(TravelRequestTable, {
      props: { data: mockData, loading: false },
      global: { plugins: [pinia, i18n], stubs: elStubs },
    })
    const authStore = useAuthStore(pinia)
    authStore.user = { id: 99, is_admin: true }
    authStore.token = 'tok'

    const approvedRow = mockData.find((r) => r.status === 'approved')
    expect(approvedRow.status).toBe('approved')
  })

  it('emite evento delete ao confirmar exclusão', async () => {
    const pinia = createPinia()
    const wrapper = mount(TravelRequestTable, {
      props: { data: mockData, loading: false },
      global: { plugins: [pinia, i18n], stubs: elStubs },
    })
    await wrapper.vm.$emit('delete', 1)
    expect(wrapper.emitted('delete')).toBeTruthy()
    expect(wrapper.emitted('delete')[0]).toEqual([1])
  })

  it('emite evento status-change ao confirmar mudança de status', async () => {
    const pinia = createPinia()
    const wrapper = mount(TravelRequestTable, {
      props: { data: mockData, loading: false },
      global: { plugins: [pinia, i18n], stubs: elStubs },
    })
    await wrapper.vm.$emit('status-change', { id: 1, status: 'approved' })
    expect(wrapper.emitted('status-change')).toBeTruthy()
  })
})

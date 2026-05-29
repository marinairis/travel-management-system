import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import TravelRequestForm from '@/components/TravelRequestForm.vue'

vi.mock('@/plugins/axios', () => ({
  default: { get: vi.fn(), post: vi.fn() },
}))

vi.mock('@/stores/destinations', () => ({
  useDestinationsStore: () => ({
    loading: false,
    getDestinationsForSelect: [
      { value: 'São Paulo, SP', label: 'São Paulo, SP' },
      { value: 'Rio de Janeiro, RJ', label: 'Rio de Janeiro, RJ' },
    ],
    getDestinations: vi.fn().mockResolvedValue([]),
  }),
}))

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      travelRequest: {
        requesterName: 'Solicitante', requesterNamePlaceholder: 'Nome...',
        requesterNameRequired: 'Nome obrigatório',
        destination: 'Destino', destinationPlaceholder: 'Selecione...',
        destinationRequired: 'Destino obrigatório',
        departureDate: 'Data de saída', datePlaceholder: 'DD/MM/YYYY',
        departureDateRequired: 'Data de saída obrigatória',
        returnDate: 'Data de retorno',
        returnDateRequired: 'Data de retorno obrigatória',
        notes: 'Observações', notesPlaceholder: 'Observações opcionais...',
        createRequest: 'Criar pedido', updateRequest: 'Atualizar pedido',
        loadDestinationsError: 'Erro ao carregar destinos',
      },
      common: { cancel: 'Cancelar' },
    },
  },
})

const elStubs = {
  ElForm: {
    template: '<form @submit.prevent><slot /></form>',
    props: ['model', 'rules', 'labelPosition'],
  },
  ElFormItem: { template: '<div><slot /></div>', props: ['label', 'prop'] },
  ElInput: {
    template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    props: ['modelValue', 'placeholder', 'type', 'rows'],
    emits: ['update:modelValue'],
  },
  ElSelectV2: {
    template: '<select><slot /></select>',
    props: ['modelValue', 'options', 'placeholder', 'filterable', 'clearable', 'loading'],
  },
  ElDatePicker: {
    template: '<input type="date" />',
    props: ['modelValue', 'type', 'placeholder', 'format', 'valueFormat', 'disabledDate'],
  },
  ElButton: {
    template: '<button @click="$emit(\'click\')"><slot /></button>',
    props: ['type', 'loading'],
    emits: ['click'],
  },
  ElRow: { template: '<div><slot /></div>', props: ['gutter'] },
  ElCol: { template: '<div><slot /></div>', props: ['xs', 'sm'] },
  ElIcon: { template: '<span />' },
  LocationFilled: { template: '<span />' },
}

function mountForm(props = {}) {
  const pinia = createPinia()
  return mount(TravelRequestForm, {
    props: { modelValue: {}, isEdit: false, ...props },
    global: {
      plugins: [pinia, i18n],
      stubs: elStubs,
    },
  })
}

describe('TravelRequestForm', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('renderiza sem erros', () => {
    const wrapper = mountForm()
    expect(wrapper.exists()).toBe(true)
  })

  it('prop isEdit=false renderiza botão "Criar pedido"', () => {
    const wrapper = mountForm({ isEdit: false })
    expect(wrapper.text()).toContain('Criar pedido')
  })

  it('prop isEdit=true renderiza botão "Atualizar pedido"', () => {
    const wrapper = mountForm({ isEdit: true })
    expect(wrapper.text()).toContain('Atualizar pedido')
  })

  it('botão cancelar emite evento cancel', async () => {
    const wrapper = mountForm()
    const cancelButton = wrapper.findAll('button').find((btn) => btn.text().includes('Cancelar'))
    expect(cancelButton).toBeTruthy()
    await cancelButton.trigger('click')
    expect(wrapper.emitted('cancel')).toBeTruthy()
  })

  it('popula formData com modelValue fornecido', () => {
    const modelValue = {
      requester_name: 'Maria Silva',
      destination: 'São Paulo, SP',
      departure_date: '2025-08-01',
      return_date: '2025-08-05',
    }
    const wrapper = mountForm({ modelValue })
    expect(wrapper.vm.formData.requester_name).toBe('Maria Silva')
    expect(wrapper.vm.formData.destination).toBe('São Paulo, SP')
  })

  it('formData inicial está vazio quando modelValue não é fornecido', () => {
    const wrapper = mountForm()
    expect(wrapper.vm.formData.requester_name).toBe('')
    expect(wrapper.vm.formData.destination).toBe('')
    expect(wrapper.vm.formData.departure_date).toBe('')
    expect(wrapper.vm.formData.return_date).toBe('')
  })
})

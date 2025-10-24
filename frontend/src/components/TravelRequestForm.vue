<template>
  <el-form
    ref="formRef"
    :model="formData"
    :rules="rules"
    label-position="top"
    @submit.prevent="handleSubmit"
  >
    <el-form-item label="Nome do Solicitante" prop="requester_name">
      <el-input v-model="formData.requester_name" placeholder="Digite o nome do solicitante" />
    </el-form-item>

    <el-form-item label="Destino" prop="destination">
      <el-select-v2
        v-model="formData.destination"
        :options="destinationOptions"
        placeholder="Selecione ou digite para buscar destino"
        style="width: 100%"
        filterable
        clearable
        :loading="destinationsStore.loading"
        @focus="loadDestinations"
      >
        <template #default="{ item }">
          <div class="destination-item">
            <el-icon><LocationFilled /></el-icon>
            <span>{{ item.label }}</span>
          </div>
        </template>
      </el-select-v2>
    </el-form-item>

    <el-row :gutter="20">
      <el-col :xs="24" :sm="12">
        <el-form-item label="Data de Ida" prop="departure_date">
          <el-date-picker
            v-model="formData.departure_date"
            type="date"
            placeholder="Selecione a data"
            style="width: 100%"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            :disabled-date="disabledDepartureDate"
          />
        </el-form-item>
      </el-col>
      <el-col :xs="24" :sm="12">
        <el-form-item label="Data de Volta" prop="return_date">
          <el-date-picker
            v-model="formData.return_date"
            type="date"
            placeholder="Selecione a data"
            style="width: 100%"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            :disabled-date="disabledReturnDate"
          />
        </el-form-item>
      </el-col>
    </el-row>

    <el-form-item label="Observações" prop="notes">
      <el-input
        v-model="formData.notes"
        type="textarea"
        :rows="4"
        placeholder="Digite observações adicionais (opcional)"
      />
    </el-form-item>

    <el-form-item>
      <el-button type="primary" @click="handleSubmit" :loading="loading">
        {{ isEdit ? 'Atualizar' : 'Criar' }} Pedido
      </el-button>
      <el-button @click="handleCancel">Cancelar</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { LocationFilled } from '@element-plus/icons-vue'
import { useDestinationsStore } from '@/stores/destinations'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({}),
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel', 'update:modelValue'])

const formRef = ref(null)
const loading = ref(false)
const destinationsStore = useDestinationsStore()

const formData = reactive({
  requester_name: '',
  destination: '',
  departure_date: '',
  return_date: '',
  notes: '',
})

const rules = {
  requester_name: [
    { required: true, message: 'Nome do solicitante é obrigatório', trigger: 'blur' },
  ],
  destination: [{ required: true, message: 'Destino é obrigatório', trigger: 'blur' }],
  departure_date: [{ required: true, message: 'Data de ida é obrigatória', trigger: 'change' }],
  return_date: [{ required: true, message: 'Data de volta é obrigatória', trigger: 'change' }],
}

const destinationOptions = computed(() => destinationsStore.getDestinationsForSelect)

watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal && Object.keys(newVal).length > 0) {
      Object.assign(formData, newVal)
    }
  },
  { immediate: true },
)

onMounted(async () => {
  // Carrega destinos quando o componente é montado
  await loadDestinations()
})

const loadDestinations = async () => {
  try {
    await destinationsStore.getDestinations()
  } catch (error) {
    console.error('Erro ao carregar destinos:', error)
  }
}

const disabledDepartureDate = (time) => {
  return time.getTime() < Date.now() - 8.64e7
}

const disabledReturnDate = (time) => {
  if (!formData.departure_date) return time.getTime() < Date.now() - 8.64e7
  const departureTime = new Date(formData.departure_date).getTime()
  return time.getTime() < departureTime
}

const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate((valid) => {
    if (valid) {
      loading.value = true
      emit('submit', { ...formData })
      loading.value = false
    }
  })
}

const handleCancel = () => {
  formRef.value?.resetFields()
  emit('cancel')
}

defineExpose({
  resetFields: () => formRef.value?.resetFields(),
})
</script>

<style scoped>
.destination-item {
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>

<template>
  <el-form
    ref="formRef"
    :model="formData"
    :rules="rules"
    label-position="top"
    @submit.prevent="handleSubmit"
  >
    <el-form-item :label="$t('travelRequest.requesterName')" prop="requester_name">
      <el-input
        v-model="formData.requester_name"
        :placeholder="$t('travelRequest.requesterNamePlaceholder')"
      />
    </el-form-item>

    <el-form-item :label="$t('travelRequest.destination')" prop="destination">
      <el-select-v2
        v-model="formData.destination"
        :options="destinationOptions"
        :placeholder="$t('travelRequest.destinationPlaceholder')"
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
        <el-form-item :label="$t('travelRequest.departureDate')" prop="departure_date">
          <el-date-picker
            v-model="formData.departure_date"
            type="date"
            :placeholder="$t('travelRequest.datePlaceholder')"
            style="width: 100%"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            :disabled-date="disabledDepartureDate"
          />
        </el-form-item>
      </el-col>

      <el-col :xs="24" :sm="12">
        <el-form-item :label="$t('travelRequest.returnDate')" prop="return_date">
          <el-date-picker
            v-model="formData.return_date"
            type="date"
            :placeholder="$t('travelRequest.datePlaceholder')"
            style="width: 100%"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            :disabled-date="disabledReturnDate"
          />
        </el-form-item>
      </el-col>
    </el-row>

    <el-form-item :label="$t('travelRequest.notes')" prop="notes">
      <el-input
        v-model="formData.notes"
        type="textarea"
        :rows="4"
        :placeholder="$t('travelRequest.notesPlaceholder')"
      />
    </el-form-item>

    <el-form-item>
      <el-button type="primary" @click="handleSubmit" :loading="loading">
        {{ isEdit ? $t('travelRequest.updateRequest') : $t('travelRequest.createRequest') }}
      </el-button>

      <el-button @click="handleCancel">{{ $t('common.cancel') }}</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { LocationFilled } from '@element-plus/icons-vue'
import { useDestinationsStore } from '@/stores/destinations'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

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
    { required: true, message: t('travelRequest.requesterNameRequired'), trigger: 'blur' },
  ],
  destination: [
    { required: true, message: t('travelRequest.destinationRequired'), trigger: 'blur' },
  ],
  departure_date: [
    { required: true, message: t('travelRequest.departureDateRequired'), trigger: 'change' },
  ],
  return_date: [
    { required: true, message: t('travelRequest.returnDateRequired'), trigger: 'change' },
  ],
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
  await loadDestinations()
})

const loadDestinations = async () => {
  try {
    await destinationsStore.getDestinations()
  } catch (error) {
    console.error(t('travelRequest.loadDestinationsError'), error)
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

<style scoped></style>

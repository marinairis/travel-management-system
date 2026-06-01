<template>
  <el-form
    ref="formRef"
    :model="formData"
    :rules="rules"
    label-position="top"
    @submit.prevent="handleSubmit"
  >
    <el-form-item :label="$t('travelRequest.requesterName')" prop="requester_name">
      <el-select
        v-model="formData.requester_name"
        :placeholder="$t('travelRequest.requesterNamePlaceholder')"
        filterable
        style="width: 100%"
        :loading="userStore.isLoading"
      >
        <el-option
          v-for="u in userStore.basicUsers"
          :key="u.id"
          :label="u.name"
          :value="u.name"
        />
      </el-select>
    </el-form-item>

    <el-form-item :label="$t('travelRequest.destination')" prop="destination">
      <el-select-v2
        v-model="formData.destination"
        :options="destinationOptions"
        :placeholder="$t('travelRequest.destinationPlaceholder')"
        style="width: 100%"
        filterable
        clearable
        :loading="destinationsStore.isLoading"
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

    <el-form-item :label="$t('travelRequest.travelType')" prop="travel_type">
      <el-select
        v-model="formData.travel_type"
        :placeholder="$t('travelRequest.travelTypePlaceholder')"
        clearable
        style="width: 100%"
      >
        <el-option :label="$t('travelRequest.travelTypeBus')" value="bus">
          <div style="display:flex;align-items:center;gap:8px">
            <el-icon><Van /></el-icon>
            <span>{{ $t('travelRequest.travelTypeBus') }}</span>
          </div>
        </el-option>
        <el-option :label="$t('travelRequest.travelTypePlane')" value="plane">
          <div style="display:flex;align-items:center;gap:8px">
            <el-icon><Promotion /></el-icon>
            <span>{{ $t('travelRequest.travelTypePlane') }}</span>
          </div>
        </el-option>
        <el-option :label="$t('travelRequest.travelTypeCar')" value="car">
          <div style="display:flex;align-items:center;gap:8px">
            <el-icon><MapLocation /></el-icon>
            <span>{{ $t('travelRequest.travelTypeCar') }}</span>
          </div>
        </el-option>
        <el-option :label="$t('travelRequest.travelTypeHotel')" value="hotel">
          <div style="display:flex;align-items:center;gap:8px">
            <el-icon><House /></el-icon>
            <span>{{ $t('travelRequest.travelTypeHotel') }}</span>
          </div>
        </el-option>
      </el-select>
    </el-form-item>

    <el-form-item :label="$t('travelRequest.dateRange')" prop="date_range">
      <el-date-picker
        v-model="formData.date_range"
        type="daterange"
        :range-separator="$t('dashboard.dateRangeSeparator')"
        :start-placeholder="$t('travelRequest.departureDate')"
        :end-placeholder="$t('travelRequest.returnDate')"
        style="width: 100%"
        format="DD/MM/YYYY"
        value-format="YYYY-MM-DD"
        :disabled-date="disabledDepartureDate"
      />
    </el-form-item>

    <el-form-item :label="$t('travelRequest.notes')" prop="notes">
      <el-input
        v-model="formData.notes"
        type="textarea"
        :rows="4"
        :placeholder="$t('travelRequest.notesPlaceholder')"
      />
    </el-form-item>

    <el-form-item class="form-actions">
      <el-button @click="handleCancel">{{ $t('common.cancel') }}</el-button>
      <el-button type="primary" @click="handleSubmit" :loading="loading">
        {{ isEdit ? $t('travelRequest.updateRequest') : $t('travelRequest.createRequest') }}
      </el-button>
    </el-form-item>
  </el-form>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { LocationFilled, Van, Promotion, MapLocation, House } from '@element-plus/icons-vue'
import { useDestinationsStore } from '@/stores/destinations'
import { useUserStore } from '@/stores/user'
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
const userStore = useUserStore()

const formData = reactive({
  requester_name: '',
  destination: '',
  travel_type: '',
  date_range: [],
  notes: '',
})

const rules = {
  requester_name: [
    { required: true, message: t('travelRequest.requesterNameRequired'), trigger: 'change' },
  ],
  destination: [
    { required: true, message: t('travelRequest.destinationRequired'), trigger: 'blur' },
  ],
  date_range: [
    {
      validator: (rule, value, callback) => {
        if (!value || value.length < 2 || !value[0] || !value[1]) {
          callback(new Error(t('travelRequest.dateRangeRequired')))
        } else {
          callback()
        }
      },
      trigger: 'change',
    },
  ],
}

const destinationOptions = computed(() => destinationsStore.getDestinationsForSelect)

watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal && Object.keys(newVal).length > 0) {
      formData.requester_name = newVal.requester_name || ''
      formData.destination = newVal.destination || ''
      formData.travel_type = newVal.travel_type || ''
      formData.notes = newVal.notes || ''
      const toDateStr = (d) => d ? (typeof d === 'string' && d.includes('T') ? d.split('T')[0] : d) : ''
      if (newVal.departure_date && newVal.return_date) {
        formData.date_range = [toDateStr(newVal.departure_date), toDateStr(newVal.return_date)]
      } else {
        formData.date_range = []
      }
    }
  },
  { immediate: true },
)

onMounted(async () => {
  const promises = [loadDestinations()]
  if (!userStore.basicUsers.length) promises.push(userStore.fetchBasicUsers())
  await Promise.all(promises)
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

const handleSubmit = async () => {
  if (!formRef.value) return

  try {
    await formRef.value.validate()
    loading.value = true
    const payload = {
      requester_name: formData.requester_name,
      destination: formData.destination,
      travel_type: formData.travel_type || null,
      departure_date: formData.date_range[0],
      return_date: formData.date_range[1],
      notes: formData.notes,
    }
    emit('submit', payload)
  } catch {
  } finally {
    loading.value = false
  }
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
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 16px;
  width: 100%;
}

.form-actions :deep(.el-form-item__content) {
  justify-content: flex-end;
}
</style>

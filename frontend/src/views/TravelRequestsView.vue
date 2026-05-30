<template>
  <div>
    <!-- Page header -->
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('travelRequest.listTitle') }}</h1>
        <p class="voa-page-sub">{{ $t('travelRequest.listSubtitle') }}</p>
      </div>
      <el-button type="primary" @click="showCreateDialog = true">
        + {{ $t('dashboard.newRequest') }}
      </el-button>
    </div>

    <!-- Filters -->
    <div class="voa-filters">
      <el-select
        v-model="filters.status"
        :placeholder="$t('common.all')"
        clearable
        style="width:160px"
        @change="handleFilter"
      >
        <el-option :label="$t('status.requested')" value="requested" />
        <el-option :label="$t('status.approved')" value="approved" />
        <el-option :label="$t('status.cancelled')" value="cancelled" />
      </el-select>

      <el-select-v2
        v-model="filters.destination"
        :options="destinationOptions"
        :placeholder="$t('dashboard.selectDestination')"
        style="width:280px"
        clearable
        filterable
        :loading="destinationsStore.loading"
        @change="handleFilter"
        @focus="loadDestinations"
      />

      <el-date-picker
        v-model="dateRange"
        type="daterange"
        :range-separator="$t('dashboard.dateRangeSeparator')"
        :start-placeholder="$t('dashboard.startDate')"
        :end-placeholder="$t('dashboard.endDate')"
        format="DD/MM/YYYY"
        value-format="YYYY-MM-DD"
        style="width:300px"
        @change="handleDateChange"
      />

      <el-button link @click="handleReset">✕ {{ $t('common.clear') }}</el-button>
    </div>

    <!-- Table -->
    <el-card shadow="never" style="padding:0">
      <TravelRequestTable
        :data="travelRequestStore.travelRequests"
        :loading="travelRequestStore.loading"
        @delete="handleDelete"
        @cancel="handleCancel"
        @status-change="handleStatusChange"
        @view="handleView"
      />
    </el-card>

    <!-- Create dialog -->
    <el-dialog
      v-model="showCreateDialog"
      :title="$t('travelRequest.title')"
      width="600px"
      :close-on-click-modal="false"
      destroy-on-close
    >
      <TravelRequestForm @submit="handleCreate" @cancel="showCreateDialog = false" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useDestinationsStore } from '@/stores/destinations'
import { useI18n } from 'vue-i18n'
import TravelRequestTable from '@/components/TravelRequestTable.vue'
import TravelRequestForm from '@/components/TravelRequestForm.vue'

const { t } = useI18n()
const router = useRouter()
const travelRequestStore = useTravelRequestStore()
const destinationsStore = useDestinationsStore()

const showCreateDialog = ref(false)
const dateRange = ref([])

const filters = reactive({
  status: '',
  destination: '',
  start_date: '',
  end_date: '',
})

const destinationOptions = computed(() => destinationsStore.getDestinationsForSelect)

const loadDestinations = async () => {
  try {
    await destinationsStore.getDestinations()
  } catch (e) {
    console.error(t('travelRequest.loadDestinationsError'), e)
  }
}

const handleFilter = () => {
  travelRequestStore.fetchTravelRequests({ ...filters })
}

const handleDateChange = (dates) => {
  if (dates) {
    filters.start_date = dates[0]
    filters.end_date = dates[1]
  } else {
    filters.start_date = ''
    filters.end_date = ''
  }
  handleFilter()
}

const handleReset = () => {
  filters.status = ''
  filters.destination = ''
  filters.start_date = ''
  filters.end_date = ''
  dateRange.value = []
  handleFilter()
}

const handleCreate = async (data) => {
  const success = await travelRequestStore.createTravelRequest(data)
  if (success) showCreateDialog.value = false
}

const handleDelete = async (id) => {
  await travelRequestStore.deleteTravelRequest(id)
}

const handleCancel = async (id) => {
  await travelRequestStore.cancelTravelRequest(id)
}

const handleStatusChange = async (id, status) => {
  await travelRequestStore.updateStatus(id, status)
}

const handleView = (row) => {
  router.push('/requests/' + row.id)
}

onMounted(async () => {
  travelRequestStore.fetchTravelRequests()
  await loadDestinations()
})
</script>

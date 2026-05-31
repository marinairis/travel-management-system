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

    <!-- Filters Card -->
    <el-card class="filter-card" shadow="never" style="margin-bottom: 14px">
      <el-form :inline="true" :model="filters">
        <el-form-item :label="$t('dashboard.status')">
          <el-select
            v-model="filters.status"
            :placeholder="$t('common.all')"
            clearable
            style="width: 160px"
            @change="handleFilter"
          >
            <el-option :label="$t('status.requested')" value="requested" />
            <el-option :label="$t('status.approved')" value="approved" />
            <el-option :label="$t('status.cancelled')" value="cancelled" />
          </el-select>
        </el-form-item>

        <el-form-item :label="$t('travelRequest.destination')">
          <el-select-v2
            v-model="filters.destination"
            :options="destinationOptions"
            :placeholder="$t('dashboard.selectDestination')"
            style="width: 280px"
            clearable
            filterable
            :loading="destinationsStore.loading"
            @change="handleFilter"
            @focus="loadDestinations"
          />
        </el-form-item>

        <el-form-item :label="$t('dashboard.dateRange')">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            :range-separator="$t('dashboard.dateRangeSeparator')"
            :start-placeholder="$t('dashboard.startDate')"
            :end-placeholder="$t('dashboard.endDate')"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            style="width: 300px"
            @change="handleDateChange"
          />
        </el-form-item>

        <el-form-item>
          <el-button :icon="Refresh" @click="handleReset"> {{ $t('common.clear') }} </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Table Card -->
    <el-card shadow="never" class="voa-table-card">
      <TravelRequestTable
        :data="travelRequestStore.travelRequests"
        :loading="travelRequestStore.loading"
        @delete="handleDelete"
        @cancel="handleCancel"
        @approve="handleApprove"
        @status-change="handleStatusChange"
        @view="handleView"
      />

      <!-- Pagination -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 20, 50]"
          :total="travelRequestStore.pagination.total"
          layout="total, sizes, prev, pager, next"
          @size-change="handlePageChange"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>

    <!-- Create Modal -->
    <el-dialog
      v-model="showCreateDialog"
      :title="$t('travelRequest.title')"
      width="550px"
      align-center
    >
      <TravelRequestForm ref="formRef" @submit="handleCreate" @cancel="showCreateDialog = false" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useDestinationsStore } from '@/stores/destinations'
import { useI18n } from 'vue-i18n'
import { Refresh } from '@element-plus/icons-vue'
import TravelRequestTable from '@/components/TravelRequestTable.vue'
import TravelRequestForm from '@/components/TravelRequestForm.vue'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const travelRequestStore = useTravelRequestStore()
const destinationsStore = useDestinationsStore()

const showCreateDialog = ref(false)
const formRef = ref(null)
const dateRange = ref([])
const currentPage = ref(1)
const pageSize = ref(10)

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

const updateQueryParams = () => {
  const query = {}
  if (filters.status) query.status = filters.status
  if (filters.destination) query.destination = filters.destination
  if (filters.start_date) query.start_date = filters.start_date
  if (filters.end_date) query.end_date = filters.end_date
  if (currentPage.value > 1) query.page = currentPage.value
  if (pageSize.value !== 10) query.per_page = pageSize.value
  
  router.replace({ query })
}

const loadFiltersFromQuery = () => {
  filters.status = route.query.status || ''
  filters.destination = route.query.destination || ''
  filters.start_date = route.query.start_date || ''
  filters.end_date = route.query.end_date || ''
  currentPage.value = parseInt(route.query.page) || 1
  pageSize.value = parseInt(route.query.per_page) || 10
  
  if (filters.start_date && filters.end_date) {
    dateRange.value = [filters.start_date, filters.end_date]
  } else {
    dateRange.value = []
  }
}

const handleFilter = () => {
  updateQueryParams()
  travelRequestStore.fetchTravelRequests({ ...filters, page: currentPage.value, per_page: pageSize.value })
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
  router.replace({ query: {} })
  handleFilter()
}

const handleCreate = async (data) => {
  const success = await travelRequestStore.createTravelRequest(data)
  if (success) showCreateDialog.value = false
}

const handleDelete = async (id) => {
  await travelRequestStore.deleteTravelRequest(id)
}

const handleCancel = async ({ id, reason }) => {
  await travelRequestStore.cancelTravelRequest(id, reason)
}

const handleApprove = async (id) => {
  await travelRequestStore.updateStatus(id, 'approved')
}

const handleStatusChange = async (id, status) => {
  await travelRequestStore.updateStatus(id, status)
}

const handleView = (row) => {
  router.push('/requests/' + row.id)
}

const handlePageChange = () => {
  updateQueryParams()
  travelRequestStore.fetchTravelRequests({
    ...filters,
    page: currentPage.value,
    per_page: pageSize.value,
  })
}

onMounted(async () => {
  loadFiltersFromQuery()
  travelRequestStore.fetchTravelRequests({ ...filters, page: currentPage.value, per_page: pageSize.value })
  await loadDestinations()
})
</script>

<style scoped>
.voa-table-card :deep(.el-table__body-wrapper) {
  overflow-y: auto;
  max-height: calc(100vh - 340px);
}

.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0 4px;
  border-top: 1px solid var(--el-border-color);
}
</style>

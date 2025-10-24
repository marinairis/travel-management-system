<template>
  <div class="page-container">
    <el-container>
      <el-main class="main-content">
        <div class="page-header">
          <h1 class="page-title">Pedidos de Viagem</h1>
          <el-button type="primary" :icon="Plus" @click="showCreateDialog = true">
            Novo Pedido
          </el-button>
        </div>

        <!-- Filtros -->
        <el-card class="filter-card" shadow="never">
          <el-form :inline="true" :model="filters">
            <el-form-item label="Status">
              <el-select
                v-model="filters.status"
                placeholder="Todos"
                clearable
                style="width: 150px"
                @change="handleFilter"
              >
                <el-option label="Solicitado" value="requested" />
                <el-option label="Aprovado" value="approved" />
                <el-option label="Cancelado" value="cancelled" />
              </el-select>
            </el-form-item>

            <el-form-item label="Destino">
              <el-select-v2
                v-model="filters.destination"
                :options="destinationOptions"
                placeholder="Selecione destino"
                style="width: 340px"
                clearable
                filterable
                :loading="destinationsStore.loading"
                @change="handleFilter"
                @focus="loadDestinations"
              >
                <template #default="{ item }">
                  <div class="destination-item">
                    <el-icon><LocationFilled /></el-icon>
                    <el-tooltip
                      v-if="isTextOverflowing(item.label)"
                      class="box-item"
                      effect="dark"
                      :content="item.label"
                      placement="right-start"
                    >
                      <span class="destination-text">{{ item.label }}</span>
                    </el-tooltip>
                    <span v-else class="destination-text">{{ item.label }}</span>
                  </div>
                </template>
              </el-select-v2>
            </el-form-item>

            <el-form-item label="Período">
              <el-date-picker
                v-model="dateRange"
                type="daterange"
                range-separator="até"
                start-placeholder="Data início"
                end-placeholder="Data fim"
                format="DD/MM/YYYY"
                value-format="YYYY-MM-DD"
                @change="handleDateChange"
              />
            </el-form-item>

            <el-form-item>
              <el-button :icon="Refresh" @click="handleReset"> Limpar </el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <!-- Tabela -->
        <el-card class="table-card">
          <TravelRequestTable
            :data="travelRequestStore.travelRequests"
            :loading="travelRequestStore.loading"
            @delete="handleDelete"
            @status-change="handleStatusChange"
            @view="handleView"
          />
        </el-card>
      </el-main>
    </el-container>

    <!-- Dialog de criação -->
    <el-dialog
      v-model="showCreateDialog"
      title="Novo Pedido de Viagem"
      width="600"
      :close-on-click-modal="false"
    >
      <TravelRequestForm @submit="handleCreate" @cancel="showCreateDialog = false" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useThemeStore } from '@/stores/theme'
import { useDestinationsStore } from '@/stores/destinations'
import { useTextUtils } from '@/composables/useTextUtils'
import TravelRequestTable from '@/components/TravelRequestTable.vue'
import TravelRequestForm from '@/components/TravelRequestForm.vue'
import { Plus, Refresh, LocationFilled } from '@element-plus/icons-vue'

const travelRequestStore = useTravelRequestStore()
const themeStore = useThemeStore()
const destinationsStore = useDestinationsStore()
const { isTextOverflowing } = useTextUtils()

const showCreateDialog = ref(false)
const dateRange = ref([])

const filters = reactive({
  status: '',
  destination: '',
  start_date: '',
  end_date: '',
})

const destinationOptions = computed(() => destinationsStore.getDestinationsForSelect)

onMounted(async () => {
  themeStore.initTheme()
  travelRequestStore.fetchTravelRequests()
  await loadDestinations()
})

const loadDestinations = async () => {
  try {
    await destinationsStore.getDestinations()
  } catch (error) {
    console.error('Erro ao carregar destinos:', error)
  }
}

const handleFilter = () => {
  travelRequestStore.fetchTravelRequests(filters)
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
  if (success) {
    showCreateDialog.value = false
  }
}

const handleDelete = async (id) => {
  await travelRequestStore.deleteTravelRequest(id)
}

const handleStatusChange = async (id, status) => {
  await travelRequestStore.updateStatus(id, status)
}

const handleView = (data) => {
  console.log('View:', data)
}
</script>

<style scoped></style>

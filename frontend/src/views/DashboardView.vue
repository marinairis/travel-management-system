<template>
  <div class="dashboard-container">
    <el-container>
      <TheHeader />

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
              <el-input
                v-model="filters.destination"
                placeholder="Buscar destino"
                clearable
                style="width: 200px"
                @change="handleFilter"
              />
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
import { ref, reactive, onMounted } from 'vue'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useThemeStore } from '@/stores/theme'
import TheHeader from '@/components/TheHeader.vue'
import TravelRequestTable from '@/components/TravelRequestTable.vue'
import TravelRequestForm from '@/components/TravelRequestForm.vue'
import { Plus, Refresh } from '@element-plus/icons-vue'

const travelRequestStore = useTravelRequestStore()
const themeStore = useThemeStore()

const showCreateDialog = ref(false)
const dateRange = ref([])

const filters = reactive({
  status: '',
  destination: '',
  start_date: '',
  end_date: '',
})

onMounted(() => {
  themeStore.initTheme()
  travelRequestStore.fetchTravelRequests()
})

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
  // Implementar se necessário
  console.log('View:', data)
}
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background-color: var(--el-bg-color-page);
}

.main-content {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  margin: 0;
}

.filter-card {
  margin-bottom: 24px;
}

.table-card {
  margin-bottom: 24px;
}

@media (max-width: 768px) {
  .main-content {
    padding: 16px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .page-title {
    font-size: 24px;
  }

  :deep(.el-form--inline .el-form-item) {
    display: block;
    margin-bottom: 12px;
  }
}

@media (min-width: 769px) {
  .main-content {
    padding: 24px;
  }

  .page-header {
    flex-direction: row;
    align-items: center;
    gap: 0;
  }

  .page-title {
    font-size: 28px;
  }
}
</style>

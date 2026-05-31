<template>
  <div v-if="loading" v-loading="true" style="height: 200px" />

  <el-empty v-else-if="!request" description="Pedido não encontrado" />

  <div v-else>
    <!-- Back + header -->
    <div class="voa-page-head">
      <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap">
        <el-button link @click="handleGoBack"> ← {{ $t('travelRequest.detailBack') }} </el-button>
        <span style="color: var(--el-border-color)">|</span>
        <span
          style="
            font-family: var(--voa-mono, monospace);
            font-size: 15px;
            font-weight: 700;
            color: var(--el-color-primary);
          "
          >{{ formatRequestId(request.id) }}</span
        >
        <el-tag :type="getStatusType(request.status)" round>
          {{ translateStatus(request.status) }}
        </el-tag>
      </div>
      <!-- Actions -->
      <div style="display: flex; gap: 8px; flex-wrap: wrap">
        <!-- Botão Editar - só mostra se for o dono do pedido -->
        <el-button
          v-if="isOwner && request.status === 'requested'"
          type="primary"
          plain
          @click="editDrawerOpen = true"
        >
          <el-icon style="margin-right: 4px"><Edit /></el-icon>
          {{ $t('common.edit') }}
        </el-button>
        <!-- Botão Cancelar - disponível para o dono do pedido ou admin -->
        <el-button
          v-if="(isOwner || authStore.isAdmin) && request.can_be_cancelled && canBeModified"
          type="danger"
          plain
          @click="cancelOpen = true"
        >
          {{ $t('travelRequest.detailCancel') }}
        </el-button>
        <!-- Botão Excluir - disponível apenas para admin -->
        <el-button
          v-if="authStore.isAdmin && canBeModified"
          type="danger"
          @click="deleteOpen = true"
        >
          <el-icon style="margin-right: 4px"><Delete /></el-icon>
          {{ $t('common.delete') }}
        </el-button>
        <el-button
          v-if="
            request.status === 'requested' &&
            authStore.isApprover &&
            request.user_id !== authStore.user?.id
          "
          type="success"
          :loading="actionLoading === 'approve'"
          @click="handleApprove"
        >
          ✓ {{ $t('travelRequest.detailApprove') }}
        </el-button>
        <el-button
          v-if="
            request.status === 'cancelled' &&
            authStore.isApprover &&
            request.user_id !== authStore.user?.id &&
            !isCancelledBySystem
          "
          @click="handleReopen"
          :loading="actionLoading === 'reopen'"
        >
          {{ $t('travelRequest.detailReopen') }}
        </el-button>
      </div>
    </div>

    <!-- Own request info -->
    <el-alert
      v-if="isOwner && request.status === 'requested'"
      type="info"
      :closable="false"
      show-icon
      style="margin-bottom: 16px"
    >
      <template #title>
        <span>{{ $t('dashboard.cannotApproveOwn') }}</span>
      </template>
    </el-alert>

    <!-- Destination banner -->
    <el-card
      shadow="never"
      style="margin-bottom: 16px; background: var(--el-color-primary-light-9)"
    >
      <div style="display: flex; align-items: center; gap: 14px">
        <div style="font-size: 32px">
          <el-icon :style="{ fontSize: '32px', color: getTravelTypeColor(request.travel_type) }">
            <component :is="travelTypeIcon(request.travel_type)" />
          </el-icon>
        </div>
        <div>
          <div style="font-size: 20px; font-weight: 800; letter-spacing: -0.02em">
            {{ request.destination }}
          </div>
          <div style="font-size: 13px; color: var(--el-text-color-secondary); margin-top: 3px">
            {{ formatDate(request.departure_date) }} → {{ formatDate(request.return_date) }}
          </div>
        </div>
      </div>
    </el-card>

    <!-- KV grid -->
    <el-card shadow="never" style="margin-bottom: 16px">
      <div class="voa-kv-grid">
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailRequester') }}</div>
          <div class="voa-kv-value">{{ request.requester_name }}</div>
        </div>
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailStatus') }}</div>
          <div class="voa-kv-value">
            <el-tag :type="getStatusType(request.status)" round>{{
              translateStatus(request.status)
            }}</el-tag>
          </div>
        </div>
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailDeparture') }}</div>
          <div class="voa-kv-value">{{ formatDate(request.departure_date) }}</div>
        </div>
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailReturn') }}</div>
          <div class="voa-kv-value">{{ formatDate(request.return_date) }}</div>
        </div>
        <div v-if="request.travel_type" class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailType') }}</div>
          <div class="voa-kv-value" style="display: flex; align-items: center; gap: 8px">
            <el-icon :style="{ color: getTravelTypeColor(request.travel_type) }">
              <component :is="travelTypeIcon(request.travel_type)" />
            </el-icon>
            {{ $t('travelRequest.travelType_' + request.travel_type) }}
          </div>
        </div>
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailCreated') }}</div>
          <div class="voa-kv-value">{{ formatDate(request.created_at) }}</div>
        </div>
        <div v-if="request.notes" class="voa-kv" style="grid-column: 1/-1">
          <div class="voa-kv-label">{{ $t('travelRequest.detailPurpose') }}</div>
          <div class="voa-kv-value">{{ request.notes }}</div>
        </div>
        <div v-if="request.approved_by" class="voa-kv" style="grid-column: 1/-1">
          <div class="voa-kv-label">{{ $t('travelRequest.approvedBy') }}</div>
          <div class="voa-kv-value">{{ request.approved_by?.name }}</div>
        </div>
      </div>
    </el-card>

    <!-- Timeline from activity logs -->
    <el-card shadow="never" v-if="timeline.length > 0">
      <template #header>
        <span style="font-weight: 700">{{ $t('travelRequest.detailTimeline') }}</span>
      </template>
      <el-timeline>
        <el-timeline-item
          v-for="(ev, i) in timeline"
          :key="i"
          :type="timelineType(ev.action)"
          :timestamp="formatDateTime(ev.created_at)"
        >
          <div style="font-weight: 600">{{ ev.description || translateAction(ev.action) }}</div>
          <div style="font-size: 12.5px; color: var(--el-text-color-secondary)">
            {{ ev.user?.name }}
          </div>
        </el-timeline-item>
      </el-timeline>
    </el-card>

    <!-- Cancel dialog -->
    <el-dialog
      v-model="cancelOpen"
      :title="$t('travelRequest.detailCancel')"
      width="440px"
      destroy-on-close
    >
      <div>
        <div
          style="
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--el-text-color-regular);
          "
        >
          {{ $t('travelRequest.detailCancelReason') }}
        </div>
        <el-input
          v-model="cancelReason"
          type="textarea"
          :rows="3"
          :placeholder="$t('travelRequest.detailCancelReasonPh')"
        />
      </div>
      <template #footer>
        <el-button @click="cancelOpen = false">{{ $t('common.cancel') }}</el-button>
        <el-button type="danger" :loading="actionLoading === 'cancel'" @click="handleCancel">
          {{ $t('travelRequest.detailCancelConfirm') }}
        </el-button>
      </template>
    </el-dialog>

    <!-- Delete dialog -->
    <el-dialog
      v-model="deleteOpen"
      :title="$t('travelRequest.confirmDelete')"
      width="400px"
      align-center
    >
      <p>{{ $t('travelRequest.deleteConfirmMessage') }}</p>
      <template #footer>
        <el-button @click="deleteOpen = false">{{ $t('common.cancel') }}</el-button>
        <el-button type="danger" :loading="actionLoading === 'delete'" @click="handleDelete">
          {{ $t('common.delete') }}
        </el-button>
      </template>
    </el-dialog>

    <!-- Edit Drawer -->
    <el-drawer
      v-model="editDrawerOpen"
      :title="$t('travelRequest.updateRequest')"
      size="500px"
      direction="rtl"
      :before-close="handleEditDrawerClose"
    >
      <TravelRequestForm
        ref="editFormRef"
        :model-value="editFormData"
        :is-edit="true"
        @submit="handleEditSubmit"
        @cancel="editDrawerOpen = false"
      />
    </el-drawer>
  </div>
</template>

<script setup>
import TravelRequestForm from '@/components/TravelRequestForm.vue'
import api from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { Delete, Edit, House, Location, MapLocation, Promotion, Van } from '@element-plus/icons-vue'
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const travelRequestStore = useTravelRequestStore()

const request = ref(null)
const timeline = ref([])
const loading = ref(true)
const cancelOpen = ref(false)
const cancelReason = ref('')
const deleteOpen = ref(false)
const actionLoading = ref(null)
const editDrawerOpen = ref(false)
const editFormRef = ref(null)

// Dados para o formulário de edição
const editFormData = ref({})

// Verificar se o usuário logado é o dono do pedido
const isOwner = computed(() => {
  return (
    request.value &&
    authStore.user &&
    (request.value.user_id === authStore.user.id ||
      request.value.requester_name === authStore.user.name)
  )
})

// Verificar se a viagem ainda não começou (pode ser cancelada/excluída)
const canBeModified = computed(() => {
  if (!request.value?.departure_date) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const departureDate = new Date(request.value.departure_date)
  return departureDate >= today
})

// Verificar se o pedido foi cancelado pelo sistema (usuário desativado)
const isCancelledBySystem = computed(() => {
  if (!request.value || request.value.status !== 'cancelled') return false
  // Verifica se tem cancel_reason que indica cancelamento pelo sistema
  const systemPatterns = ['usuário desativado', 'usuário excluído', 'Usuário desativado', 'Usuário excluído']
  return systemPatterns.some(pattern => 
    request.value.cancel_reason?.includes(pattern)
  )
})

// Ícone do tipo de viagem
const travelTypeIcon = (type) => {
  return { aereo: Promotion, onibus: Van, carro: MapLocation, hotel: House }[type] || Location
}

// Cor do tipo de viagem - usando variáveis CSS do tema
const getTravelTypeColor = (type) => {
  return (
    {
      aereo: 'var(--travel-type-aereo)',
      onibus: 'var(--travel-type-onibus)',
      carro: 'var(--travel-type-carro)',
      hotel: 'var(--travel-type-hotel)',
    }[type] || 'var(--el-color-primary)'
  )
}

// Formatar ID do pedido no formato VG-XXX
const formatRequestId = (id) => {
  if (!id) return '-'
  return `VG-${String(id).padStart(3, '0')}`
}

// Voltar para a página anterior ou para a dashboard
const handleGoBack = () => {
  if (window.history.length > 2) {
    router.back()
  } else {
    router.push('/')
  }
}

const getStatusType = (status) =>
  ({ requested: 'warning', approved: 'success', cancelled: 'danger' })[status] || ''

const translateStatus = (status) =>
  ({
    requested: t('status.requested'),
    approved: t('status.approved'),
    cancelled: t('status.cancelled'),
  })[status] || status

const translateAction = (action) =>
  ({
    create: t('activityLogs.create'),
    update: t('activityLogs.update'),
    status_change: t('activityLogs.statusChange'),
    cancel: t('activityLogs.cancel'),
  })[action] || action

const timelineType = (action) =>
  ({ create: 'primary', status_change: 'success', cancel: 'danger' })[action] || 'primary'

const formatDate = (d) => {
  if (!d) return '-'
  const s = typeof d === 'string' && d.includes('T') ? d.split('T')[0] : d
  return new Date(s + 'T12:00:00').toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const formatDateTime = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleString('pt-BR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const fetchRequest = async () => {
  loading.value = true
  try {
    const res = await api.get(`/travel-requests/${route.params.id}`)
    if (res.data.success) {
      request.value = res.data.data
      // Preparar dados para edição
      editFormData.value = {
        requester_name: request.value.requester_name,
        destination: request.value.destination,
        travel_type: request.value.travel_type,
        notes: request.value.notes,
        departure_date: request.value.departure_date,
        return_date: request.value.return_date,
      }
    }
  } catch {
    request.value = null
  }

  try {
    const logsRes = await api.get('/activity-logs', {
      params: { model_id: route.params.id, model_type: 'App\\Models\\TravelRequest', per_page: 20 },
    })
    if (logsRes.data.success) {
      timeline.value = logsRes.data.data?.data || []
    }
  } catch {
    timeline.value = []
  }

  loading.value = false
}

const handleApprove = async () => {
  actionLoading.value = 'approve'
  await travelRequestStore.updateStatus(route.params.id, 'approved')
  actionLoading.value = null
  await fetchRequest()
}

const handleCancel = async () => {
  actionLoading.value = 'cancel'
  await travelRequestStore.cancelTravelRequest(route.params.id)
  actionLoading.value = null
  cancelOpen.value = false
  cancelReason.value = ''
  await fetchRequest()
}

const handleReopen = async () => {
  actionLoading.value = 'reopen'
  await travelRequestStore.updateStatus(route.params.id, 'requested')
  actionLoading.value = null
  await fetchRequest()
}

const handleDelete = async () => {
  actionLoading.value = 'delete'
  await travelRequestStore.deleteTravelRequest(route.params.id)
  actionLoading.value = null
  deleteOpen.value = false
  router.push('/')
}

const handleEditDrawerClose = (done) => {
  editFormRef.value?.resetFields()
  done()
}

const handleEditSubmit = async (data) => {
  loading.value = true
  const success = await travelRequestStore.updateTravelRequest(route.params.id, data)
  loading.value = false
  if (success) {
    editDrawerOpen.value = false
    await fetchRequest()
  }
}

onMounted(fetchRequest)
</script>

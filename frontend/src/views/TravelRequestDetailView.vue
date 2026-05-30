<template>
  <div v-if="loading" v-loading="true" style="height:200px" />

  <el-empty v-else-if="!request" description="Pedido não encontrado" />

  <div v-else>
    <!-- Back + header -->
    <div class="voa-page-head">
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <el-button link @click="$router.push('/requests')">
          ← {{ $t('travelRequest.detailBack') }}
        </el-button>
        <span style="color:var(--el-border-color)">|</span>
        <span style="font-size:15px;font-weight:700;color:var(--el-text-color-secondary)">#{{ request.id }}</span>
        <el-tag :type="getStatusType(request.status)" round>
          {{ translateStatus(request.status) }}
        </el-tag>
      </div>
      <!-- Actions -->
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <el-button
          v-if="request.status === 'requested' && authStore.isApprover"
          type="success"
          :loading="actionLoading === 'approve'"
          @click="handleApprove"
        >
          ✓ {{ $t('travelRequest.detailApprove') }}
        </el-button>
        <el-button
          v-if="request.status === 'requested' && authStore.isApprover"
          type="danger"
          plain
          @click="cancelOpen = true"
        >
          {{ $t('travelRequest.detailCancel') }}
        </el-button>
        <el-button
          v-if="request.status === 'cancelled' && authStore.isApprover"
          @click="handleReopen"
          :loading="actionLoading === 'reopen'"
        >
          {{ $t('travelRequest.detailReopen') }}
        </el-button>
      </div>
    </div>

    <!-- Destination banner -->
    <el-card shadow="never" style="margin-bottom:16px;background:var(--el-color-primary-light-9)">
      <div style="display:flex;align-items:center;gap:14px">
        <div style="font-size:32px">✈</div>
        <div>
          <div style="font-size:20px;font-weight:800;letter-spacing:-.02em">{{ request.destination }}</div>
          <div style="font-size:13px;color:var(--el-text-color-secondary);margin-top:3px">
            {{ formatDate(request.departure_date) }} → {{ formatDate(request.return_date) }}
          </div>
        </div>
      </div>
    </el-card>

    <!-- KV grid -->
    <el-card shadow="never" style="margin-bottom:16px">
      <div class="voa-kv-grid">
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailRequester') }}</div>
          <div class="voa-kv-value">{{ request.requester_name }}</div>
        </div>
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailStatus') }}</div>
          <div class="voa-kv-value">
            <el-tag :type="getStatusType(request.status)" round>{{ translateStatus(request.status) }}</el-tag>
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
          <div class="voa-kv-value">{{ $t('travelRequest.travelType_' + request.travel_type) }}</div>
        </div>
        <div class="voa-kv">
          <div class="voa-kv-label">{{ $t('travelRequest.detailCreated') }}</div>
          <div class="voa-kv-value">{{ formatDate(request.created_at) }}</div>
        </div>
        <div v-if="request.notes" class="voa-kv" style="grid-column:1/-1">
          <div class="voa-kv-label">{{ $t('travelRequest.detailPurpose') }}</div>
          <div class="voa-kv-value">{{ request.notes }}</div>
        </div>
        <div v-if="request.approved_by" class="voa-kv" style="grid-column:1/-1">
          <div class="voa-kv-label">{{ $t('travelRequest.approvedBy') }}</div>
          <div class="voa-kv-value">{{ request.approved_by?.name }}</div>
        </div>
      </div>
    </el-card>

    <!-- Timeline from activity logs -->
    <el-card shadow="never" v-if="timeline.length > 0">
      <template #header>
        <span style="font-weight:700">{{ $t('travelRequest.detailTimeline') }}</span>
      </template>
      <el-timeline>
        <el-timeline-item
          v-for="(ev, i) in timeline"
          :key="i"
          :type="timelineType(ev.action)"
          :timestamp="formatDateTime(ev.created_at)"
        >
          <div style="font-weight:600">{{ translateAction(ev.action) }}</div>
          <div style="font-size:12.5px;color:var(--el-text-color-secondary)">{{ ev.user?.name }}</div>
        </el-timeline-item>
      </el-timeline>
    </el-card>

    <!-- Cancel dialog -->
    <el-dialog v-model="cancelOpen" :title="$t('travelRequest.detailCancel')" width="440px" destroy-on-close>
      <div>
        <div style="font-size:13px;font-weight:500;margin-bottom:6px;color:var(--el-text-color-regular)">
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useI18n } from 'vue-i18n'
import api from '@/plugins/axios'

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
const actionLoading = ref(null)

const getStatusType = (status) =>
  ({ requested: 'warning', approved: 'success', cancelled: 'danger' }[status] || '')

const translateStatus = (status) =>
  ({ requested: t('status.requested'), approved: t('status.approved'), cancelled: t('status.cancelled') }[status] || status)

const translateAction = (action) =>
  ({ create: t('activityLogs.create'), update: t('activityLogs.update'), status_change: t('activityLogs.statusChange'), cancel: t('activityLogs.cancel') }[action] || action)

const timelineType = (action) =>
  ({ create: 'primary', status_change: 'success', cancel: 'danger' }[action] || 'primary')

const formatDate = (d) => {
  if (!d) return '-'
  const s = typeof d === 'string' && d.includes('T') ? d.split('T')[0] : d
  return new Date(s + 'T12:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatDateTime = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleString('pt-BR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const fetchRequest = async () => {
  loading.value = true
  try {
    const res = await api.get(`/travel-requests/${route.params.id}`)
    if (res.data.success) {
      request.value = res.data.data
    }
  } catch {
    request.value = null
  }

  try {
    const logsRes = await api.get('/activity-logs', {
      params: { model_id: route.params.id, model_type: 'App\\Models\\TravelRequest', per_page: 20 }
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

onMounted(fetchRequest)
</script>

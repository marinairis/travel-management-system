<template>
  <div v-if="loading" v-loading="true" style="height: 200px" />

  <el-empty v-else-if="!request" :description="$t('travelRequest.notFound')" />

  <div v-else>
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
      <div style="display: flex; gap: 8px; flex-wrap: wrap">
        <el-button
          v-if="canApproveRequest"
          type="success"
          :loading="actionLoading === 'approve'"
          @click="handleApprove"
        >
          ✓ {{ $t('travelRequest.detailApprove') }}
        </el-button>
        <el-button
          v-if="canEditRequest"
          type="primary"
          plain
          @click="editDialogOpen = true"
        >
          <el-icon style="margin-right: 4px"><Edit /></el-icon>
          {{ $t('common.edit') }}
        </el-button>
        <el-button
          v-if="canCancelRequest"
          type="danger"
          plain
          @click="cancelOpen = true"
        >
          {{ $t('travelRequest.detailCancel') }}
        </el-button>
      </div>
    </div>

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

    <CancelRequestDialog
      v-model="cancelOpen"
      :is-loading="actionLoading === 'cancel'"
      confirm-type="danger"
      @confirm="handleCancel"
    />

    <el-dialog
      v-model="editDialogOpen"
      :title="$t('travelRequest.updateRequest')"
      width="550px"
      align-center
    >
      <TravelRequestForm
        ref="editFormRef"
        :model-value="editFormData"
        :is-edit="true"
        @submit="handleEditSubmit"
        @cancel="editDialogOpen = false"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import TravelRequestForm from '@/components/TravelRequestForm.vue'
import CancelRequestDialog from '@/components/CancelRequestDialog.vue'
import { useAuthStore } from '@/stores/auth'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useActivityLogStore } from '@/stores/activityLog'
import { Edit } from '@element-plus/icons-vue'
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useDateFormat } from '@/composables/useDateFormat'
import { useTravelType } from '@/composables/useTravelType'
import { useRequestStatus } from '@/composables/useRequestStatus'

const { t } = useI18n()
const { formatDateWithYear: formatDate, formatDateTimeCompact: formatDateTime } = useDateFormat()
const { travelTypeIcon, getTravelTypeColor, formatRequestId } = useTravelType()
const { getStatusType, translateStatus, isSystemCancellation } = useRequestStatus()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const travelRequestStore = useTravelRequestStore()
const activityLogStore = useActivityLogStore()

const request = ref(null)
const timeline = ref([])
const loading = ref(true)
const cancelOpen = ref(false)
const actionLoading = ref(null)
const editDialogOpen = ref(false)
const editFormRef = ref(null)

const editFormData = ref({})

const isOwner = computed(() => {
  return (
    request.value &&
    authStore.user &&
    (request.value.user_id === authStore.user.id ||
      request.value.requester_name === authStore.user.name)
  )
})

const canApproveRequest = computed(() => {
  if (!request.value) return false
  if (!authStore.isApprover) return false
  if (request.value.user_id === authStore.user?.id) return false
  return request.value.status === 'requested'
})

const canEditRequest = computed(() => {
  if (!request.value) return false
  if (request.value.status !== 'requested') return false
  if (!authStore.isApprover) return false
  if (request.value.user_id === authStore.user?.id) return false
  return true
})

const canCancelRequest = computed(() => {
  if (!request.value) return false
  if (request.value.status === 'expired') return false
  if (!['requested', 'approved'].includes(request.value.status)) return false
  if (!authStore.isApprover) return false
  if (request.value.user_id === authStore.user?.id) return false
  if (request.value.can_be_cancelled === false) return false
  if (request.value.departure_date) {
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const departureDate = new Date(request.value.departure_date)
    if (departureDate < today) return false
  }
  return true
})

const isCancelledBySystem = computed(() =>
  isSystemCancellation(request.value?.status, request.value?.cancel_reason)
)

const handleGoBack = () => {
  if (window.history.length > 2) {
    router.back()
  } else {
    router.push('/')
  }
}

const translateAction = (action) =>
  ({
    create: t('activityLogs.create'),
    update: t('activityLogs.update'),
    status_change: t('activityLogs.statusChange'),
    cancel: t('activityLogs.cancel'),
  })[action] || action

const timelineType = (action) =>
  ({ create: 'primary', status_change: 'success', cancel: 'danger' })[action] || 'primary'

const fetchRequest = async () => {
  loading.value = true
  const data = await travelRequestStore.fetchRequestDetail(route.params.id)
  request.value = data
  if (data) {
    editFormData.value = {
      requester_name: data.requester_name,
      destination: data.destination,
      travel_type: data.travel_type,
      notes: data.notes,
      departure_date: data.departure_date,
      return_date: data.return_date,
    }
  }
  timeline.value = await activityLogStore.fetchForRequest(route.params.id)
  loading.value = false
}

const handleApprove = async () => {
  actionLoading.value = 'approve'
  await travelRequestStore.updateStatus(route.params.id, 'approved')
  actionLoading.value = null
  await fetchRequest()
}

const handleCancel = async (reason) => {
  actionLoading.value = 'cancel'
  await travelRequestStore.cancelTravelRequest(route.params.id, reason)
  actionLoading.value = null
  cancelOpen.value = false
  await fetchRequest()
}

const handleEditSubmit = async (data) => {
  loading.value = true
  const success = await travelRequestStore.updateTravelRequest(route.params.id, data)
  loading.value = false
  if (success) {
    editDialogOpen.value = false
    await fetchRequest()
  }
}

onMounted(fetchRequest)
</script>

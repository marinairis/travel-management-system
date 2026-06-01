<template>
  <div>
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('dashboard.title') }}</h1>
        <p class="voa-page-sub">{{ $t('dashboard.subtitle') }}</p>
      </div>
      <el-button v-if="!authStore.isAdmin" type="primary" @click="showCreateDialog = true">
        <el-icon style="margin-right: 6px"><Plus /></el-icon>
        {{ $t('dashboard.newRequest') }}
      </el-button>
    </div>

    <div class="voa-stats-grid">
      <el-card shadow="never">
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
          "
        >
          <div class="voa-stat-label">{{ $t('dashboard.statTotal') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-primary); opacity: 0.7"
            ><DataLine
          /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.total }}</div>
      </el-card>
      <el-card shadow="never">
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
          "
        >
          <div class="voa-stat-label">{{ $t('dashboard.statPending') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-warning); opacity: 0.8"
            ><Clock
          /></el-icon>
        </div>
        <div class="voa-stat-val accent">{{ stats.pending }}</div>
      </el-card>
      <el-card shadow="never">
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
          "
        >
          <div class="voa-stat-label">{{ $t('dashboard.statApproved') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-success); opacity: 0.8"
            ><CircleCheck
          /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.approved }}</div>
      </el-card>
      <el-card shadow="never">
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
          "
        >
          <div class="voa-stat-label">{{ $t('dashboard.statCancelled') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-danger); opacity: 0.8"
            ><CircleClose
          /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.cancelled }}</div>
      </el-card>
    </div>

    <div class="voa-stats-grid" style="margin-bottom: 20px">
      <el-card shadow="never" v-for="type in travelTypeStats" :key="type.key">
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
          "
        >
          <div class="voa-stat-label">{{ $t('travelRequest.travelType_' + type.key) }}</div>
          <el-icon :style="{ fontSize: '22px', color: type.color, opacity: 0.85 }">
            <component :is="type.icon" />
          </el-icon>
        </div>
        <div class="voa-stat-val" :style="{ color: type.color }">{{ type.count }}</div>
      </el-card>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px">
      <el-card shadow="never" v-loading="travelRequestStore.isLoadingDashboard">
        <template #header>
          <div style="display: flex; align-items: center; justify-content: space-between">
            <div style="display: flex; align-items: center; gap: 8px">
              <el-icon style="color: var(--el-color-warning)"><Warning /></el-icon>
              <span style="font-weight: 700">{{ $t('dashboard.pendingApproval') }}</span>
            </div>
            <span style="font-size: 12px; color: var(--el-text-color-secondary)">
              {{ $t('dashboard.oldestPending') }}
            </span>
          </div>
        </template>
        <el-empty
          v-if="pendingApproval.length === 0"
          :description="$t('dashboard.pendingEmpty')"
          :image-size="50"
        />
        <div
          v-for="req in pendingApproval"
          :key="req.id"
          style="
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid var(--el-border-color);
          "
        >
          <el-tooltip :content="$t('travelRequest.travelType_' + req.travel_type)" placement="top">
            <el-icon style="color: var(--el-text-color-secondary); flex-shrink: 0">
              <component :is="travelTypeIcon(req.travel_type)" />
            </el-icon>
          </el-tooltip>
          <div style="flex: 1; cursor: pointer" @click="$router.push('/requests/' + req.id)">
            <div style="display: flex; align-items: center; gap: 6px">
              <span
                style="
                  font-family: var(--voa-mono, monospace);
                  font-size: 11px;
                  font-weight: 700;
                  color: var(--el-color-primary);
                "
                >{{ formatRequestId(req.id) }}</span
              >
              <span style="font-weight: 600; font-size: 13.5px">{{ req.destination }}</span>
            </div>
            <div style="font-size: 12px; color: var(--el-text-color-secondary)">
              {{ req.requester_name }} · {{ formatDate(req.departure_date) }}
            </div>
          </div>
          <template v-if="authStore.isApprover && req.user_id !== authStore.user?.id">
            <el-tooltip :content="$t('dashboard.approve')" placement="top">
              <el-icon
                style="color: var(--el-color-success); cursor: pointer; font-size: 18px"
                @click.stop="handleApprove(req)"
              >
                <SuccessFilled />
              </el-icon>
            </el-tooltip>
            <el-tooltip :content="$t('common.cancel')" placement="top">
              <el-icon
                style="color: var(--el-color-danger); cursor: pointer; font-size: 18px"
                @click.stop="handleCancel(req)"
              >
                <CircleCloseFilled />
              </el-icon>
            </el-tooltip>
          </template>
          <el-tooltip v-else-if="authStore.isApprover && req.user_id === authStore.user?.id" :content="$t('dashboard.cannotApproveOwn')" placement="top">
            <el-icon style="color: var(--el-text-color-secondary); cursor: help; font-size: 18px">
              <Lock />
            </el-icon>
          </el-tooltip>
        </div>
      </el-card>

      <el-card shadow="never" v-loading="travelRequestStore.isLoadingDashboard">
        <template #header>
          <div style="display: flex; align-items: center; justify-content: space-between">
            <div style="display: flex; align-items: center; gap: 8px">
              <el-icon style="color: var(--el-color-primary)"><Clock /></el-icon>
              <span style="font-weight: 700">{{ $t('dashboard.recentRequests') }}</span>
            </div>
            <span style="font-size: 12px; color: var(--el-text-color-secondary)">
              {{ $t('dashboard.mostRecent') }}
            </span>
          </div>
        </template>
        <el-empty
          v-if="travelRequestStore.recentRequests.length === 0"
          :description="$t('dashboard.recentEmpty')"
          :image-size="50"
        />
        <div
          v-for="req in travelRequestStore.recentRequests"
          :key="req.id"
          style="
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid var(--el-border-color);
            cursor: pointer;
          "
          @click="$router.push('/requests/' + req.id)"
        >
          <el-tooltip :content="$t('travelRequest.travelType_' + req.travel_type)" placement="top">
            <el-icon style="color: var(--el-text-color-secondary); flex-shrink: 0">
              <component :is="travelTypeIcon(req.travel_type)" />
            </el-icon>
          </el-tooltip>
          <div style="flex: 1; min-width: 0">
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 2px">
              <span
                style="
                  font-family: var(--voa-mono, monospace);
                  font-size: 11px;
                  font-weight: 700;
                  color: var(--el-color-primary);
                "
                >{{ formatRequestId(req.id) }}</span
              >
              <span
                style="
                  font-weight: 600;
                  font-size: 13px;
                  white-space: nowrap;
                  overflow: hidden;
                  text-overflow: ellipsis;
                "
              >
                {{ req.destination }}
              </span>
            </div>
            <div style="font-size: 12px; color: var(--el-text-color-secondary)">
              {{ req.requester_name }} · {{ formatDate(req.departure_date) }}
            </div>
          </div>
          <el-tag :type="getStatusType(req.status)" size="small">
            {{ translateStatus(req.status) }}
          </el-tag>
        </div>
      </el-card>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px">
      <el-card shadow="never">
        <template #header>
          <div style="display: flex; align-items: center; gap: 8px">
            <el-icon style="color: var(--el-color-primary)"><TrophyBase /></el-icon>
            <span style="font-weight: 700">{{ $t('dashboard.topDestinations') }}</span>
          </div>
        </template>
        <el-empty
          v-if="topDestinations.length === 0"
          :description="$t('dashboard.recentEmpty')"
          :image-size="50"
        />
        <div
          v-for="(dest, i) in topDestinations"
          :key="dest.name"
          style="
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid var(--el-border-color);
          "
        >
          <span
            :style="{
              width: '22px',
              height: '22px',
              borderRadius: '50%',
              background: i < 3 ? 'var(--el-color-primary)' : 'var(--el-fill-color)',
              color: i < 3 ? '#fff' : 'var(--el-text-color-secondary)',
              display: 'grid',
              placeItems: 'center',
              fontSize: '11px',
              fontWeight: 700,
              flexShrink: 0,
            }"
            >{{ i + 1 }}</span
          >
          <div style="flex: 1; min-width: 0">
            <div
              style="
                font-size: 13px;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
              "
            >
              <el-icon style="font-size: 11px; margin-right: 3px; vertical-align: middle"
                ><Location
              /></el-icon>
              {{ dest.name }}
            </div>
          </div>
          <el-tag type="info" size="small" round>{{ dest.count }}</el-tag>
        </div>
      </el-card>

      <el-card shadow="never">
        <template #header>
          <div style="display: flex; align-items: center; gap: 8px">
            <el-icon style="color: var(--el-color-primary)"><Van /></el-icon>
            <span style="font-weight: 700">{{ $t('dashboard.travelTypesMostUsed') }}</span>
          </div>
        </template>
        <el-empty
          v-if="travelTypeStats.every((t) => t.count === 0)"
          :description="$t('dashboard.noTravelTypes')"
          :image-size="50"
        />
        <div
          v-for="type in sortedTravelTypes"
          :key="type.key"
          style="
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--el-border-color);
          "
        >
          <div
            :style="{
              width: '36px',
              height: '36px',
              borderRadius: '8px',
              background: type.color + '20',
              display: 'grid',
              placeItems: 'center',
              flexShrink: 0,
            }"
          >
            <el-tooltip :content="$t('travelRequest.travelType_' + type.key)" placement="top">
              <el-icon :style="{ fontSize: '18px', color: type.color }">
                <component :is="type.icon" />
              </el-icon>
            </el-tooltip>
          </div>
          <div style="flex: 1">
            <div style="font-size: 13px; font-weight: 600">
              {{ $t('travelRequest.travelType_' + type.key) }}
            </div>
            <div style="font-size: 12px; color: var(--el-text-color-secondary)">
              {{ type.percentage }}% {{ $t('dashboard.ofTotalRequests') }}
            </div>
          </div>
          <div style="text-align: right">
            <div style="font-size: 20px; font-weight: 800; color: var(--el-text-color-primary)">
              {{ type.count }}
            </div>
            <div style="font-size: 11px; color: var(--el-text-color-secondary)">
              {{ $t('dashboard.requests') }}
            </div>
          </div>
        </div>
      </el-card>
    </div>

    <el-dialog
      v-model="showCreateDialog"
      :title="$t('travelRequest.title')"
      width="550px"
      align-center
      destroy-on-close
    >
      <TravelRequestForm @submit="handleCreate" @cancel="showCreateDialog = false" />
    </el-dialog>

    <el-dialog v-model="cancelDialogVisible" :title="$t('travelRequest.cancelTitle')" width="450px" align-center>
      <p>{{ $t('travelRequest.cancelConfirmMessage') }}</p>
      <el-form-item :label="$t('travelRequest.cancelReasonLabel')" required style="margin-top: 16px;">
        <el-input
          v-model="cancelReason"
          type="textarea"
          :rows="3"
          :placeholder="$t('travelRequest.cancelReasonPlaceholder')"
        />
      </el-form-item>
      <template #footer>
        <el-button @click="cancelDialogVisible = false">{{ $t('common.cancel') }}</el-button>
        <el-button type="warning" @click="confirmCancel" :loading="cancellingId" :disabled="!cancelReason.trim()">
          {{ $t('common.confirm') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useUserStore } from '@/stores/user'
import { useDestinationsStore } from '@/stores/destinations'
import { useI18n } from 'vue-i18n'
import TravelRequestForm from '@/components/TravelRequestForm.vue'
import {
  Plus,
  DataLine,
  Clock,
  CircleCheck,
  CircleClose,
  Warning,
  Location,
  TrophyBase,
  Van,
  Promotion,
  MapLocation,
  House,
  Lock,
  SuccessFilled,
  CircleCloseFilled,
} from '@element-plus/icons-vue'
import { useDateFormat } from '@/composables/useDateFormat'
import { useTravelType } from '@/composables/useTravelType'
import { useRequestStatus } from '@/composables/useRequestStatus'

const { t } = useI18n()
const { formatDate } = useDateFormat()
const { travelTypeIcon, formatRequestId } = useTravelType()
const { getStatusType, translateStatus } = useRequestStatus()
const travelRequestStore = useTravelRequestStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const userStore = useUserStore()
const destinationsStore = useDestinationsStore()

const showCreateDialog = ref(false)
const approvingId = ref(null)
const cancellingId = ref(null)
const cancelDialogVisible = ref(false)
const cancelReason = ref('')
const selectedRequest = ref(null)

const pendingApproval = computed(() => travelRequestStore.pendingApproval)

const dashboardStats = computed(() => travelRequestStore.dashboardStats)

const stats = computed(() => {
  const byStatus = dashboardStats.value.by_status || {}
  return {
    total: dashboardStats.value.total || 0,
    pending: byStatus.requested || 0,
    approved: byStatus.approved || 0,
    cancelled: byStatus.cancelled || 0,
    expired: byStatus.expired || 0,
  }
})

const travelTypeStats = computed(() => {
  const byType = dashboardStats.value.by_travel_type || {}
  return [
    { key: 'plane', icon: Promotion,  color: 'var(--travel-type-plane)', count: byType.plane || 0 },
    { key: 'bus',   icon: Van,         color: 'var(--travel-type-bus)',   count: byType.bus   || 0 },
    { key: 'car',   icon: MapLocation, color: 'var(--travel-type-car)',   count: byType.car   || 0 },
    { key: 'hotel', icon: House,       color: 'var(--travel-type-hotel)', count: byType.hotel || 0 },
  ]
})

const sortedTravelTypes = computed(() => {
  const total = stats.value.total || 1
  return [...travelTypeStats.value]
    .sort((a, b) => b.count - a.count)
    .map((type) => ({
      ...type,
      percentage: Math.round((type.count / total) * 100),
    }))
})

const topDestinations = computed(() => dashboardStats.value.top_destinations || [])


const handleApprove = async (req) => {
  approvingId.value = req.id
  await travelRequestStore.updateStatus(req.id, 'approved')
  approvingId.value = null
  travelRequestStore.fetchPendingApproval()
  travelRequestStore.fetchDashboardStats()
}

const handleCancel = (req) => {
  selectedRequest.value = req
  cancelReason.value = ''
  cancelDialogVisible.value = true
}

const confirmCancel = async () => {
  cancellingId.value = true
  await travelRequestStore.cancelWithReason(selectedRequest.value.id, cancelReason.value)
  cancellingId.value = false
  cancelReason.value = ''
  cancelDialogVisible.value = false
  selectedRequest.value = null
  travelRequestStore.fetchPendingApproval()
  travelRequestStore.fetchDashboardStats()
}

const handleCreate = async (data) => {
  const success = await travelRequestStore.createTravelRequest(data)
  if (success) showCreateDialog.value = false
}

onMounted(() => {
  themeStore.initTheme()
  travelRequestStore.fetchDashboardStats()
  travelRequestStore.fetchPendingApproval()
  travelRequestStore.fetchRecentRequests()
  userStore.fetchBasicUsers()
  destinationsStore.getDestinations()
})
</script>

<style scoped>
@media (max-width: 768px) {
  div[style*='grid-template-columns:1fr 1fr'] {
    grid-template-columns: 1fr !important;
  }
}

:deep(.voa-page-head + .voa-stats-grid + .voa-stats-grid + div .el-tag),
:deep(.voa-stats-grid ~ div .el-tag) {
  min-width: 80px;
  text-align: center;
}
</style>

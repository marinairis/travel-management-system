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

    <DashboardStatsGrid :stats="stats" :travel-type-stats="travelTypeStats" />

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px">
      <DashboardPendingApproval
        :requests="pendingApproval"
        :is-loading="dashboardStore.isLoading"
        :is-approver="authStore.isApprover"
        :current-user-id="authStore.user?.id"
        @approve="handleApprove"
        @cancel="handleCancel"
      />

      <DashboardRecentRequests
        :requests="dashboardStore.recentRequests"
        :is-loading="dashboardStore.isLoading"
      />
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

    <CancelRequestDialog
      v-model="cancelDialogVisible"
      :is-loading="!!cancellingId"
      @confirm="confirmCancel"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useUserStore } from '@/stores/user'
import { useDestinationsStore } from '@/stores/destinations'
import TravelRequestForm from '@/components/TravelRequestForm.vue'
import DashboardStatsGrid from '@/components/DashboardStatsGrid.vue'
import DashboardPendingApproval from '@/components/DashboardPendingApproval.vue'
import DashboardRecentRequests from '@/components/DashboardRecentRequests.vue'
import CancelRequestDialog from '@/components/CancelRequestDialog.vue'
import { Plus, Location, TrophyBase, Van, Promotion, MapLocation, House } from '@element-plus/icons-vue'

const travelRequestStore = useTravelRequestStore()
const dashboardStore = useDashboardStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const userStore = useUserStore()
const destinationsStore = useDestinationsStore()

const showCreateDialog = ref(false)
const approvingId = ref(null)
const cancellingId = ref(false)
const cancelDialogVisible = ref(false)
const selectedRequest = ref(null)

const pendingApproval = computed(() => dashboardStore.pendingApproval)

const stats = computed(() => {
  const byStatus = dashboardStore.stats.by_status || {}
  return {
    total: dashboardStore.stats.total || 0,
    pending: byStatus.requested || 0,
    approved: byStatus.approved || 0,
    cancelled: byStatus.cancelled || 0,
    expired: byStatus.expired || 0,
  }
})

const travelTypeStats = computed(() => {
  const byType = dashboardStore.stats.by_travel_type || {}
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

const topDestinations = computed(() => dashboardStore.stats.top_destinations || [])


const handleApprove = async (req) => {
  approvingId.value = req.id
  await travelRequestStore.updateStatus(req.id, 'approved')
  approvingId.value = null
  dashboardStore.fetchPendingApproval()
  dashboardStore.fetchStats()
}

const handleCancel = (req) => {
  selectedRequest.value = req
  cancelDialogVisible.value = true
}

const confirmCancel = async (reason) => {
  cancellingId.value = true
  await travelRequestStore.cancelTravelRequest(selectedRequest.value.id, reason)
  cancellingId.value = false
  cancelDialogVisible.value = false
  selectedRequest.value = null
  dashboardStore.fetchPendingApproval()
  dashboardStore.fetchStats()
}

const handleCreate = async (data) => {
  const success = await travelRequestStore.createTravelRequest(data)
  if (success) showCreateDialog.value = false
}

onMounted(() => {
  themeStore.initTheme()
  dashboardStore.fetchStats()
  dashboardStore.fetchPendingApproval()
  dashboardStore.fetchRecentRequests()
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

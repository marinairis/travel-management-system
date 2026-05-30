<template>
  <div>
    <!-- Page header -->
    <div class="voa-page-head">
      <div>
        <h1 class="voa-page-title">{{ $t('dashboard.title') }}</h1>
        <p class="voa-page-sub">{{ $t('dashboard.subtitle') }}</p>
      </div>
      <el-button v-if="!authStore.isAdmin" type="primary" @click="showCreateDialog = true">
        <el-icon style="margin-right:6px"><Plus /></el-icon>
        {{ $t('dashboard.newRequest') }}
      </el-button>
    </div>

    <!-- Stats grid (status) -->
    <div class="voa-stats-grid">
      <el-card shadow="never">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div class="voa-stat-label">{{ $t('dashboard.statTotal') }}</div>
          <el-icon style="font-size:22px;color:var(--el-color-primary);opacity:.7"><DataLine /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.total }}</div>
      </el-card>
      <el-card shadow="never">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div class="voa-stat-label">{{ $t('dashboard.statPending') }}</div>
          <el-icon style="font-size:22px;color:var(--el-color-warning);opacity:.8"><Clock /></el-icon>
        </div>
        <div class="voa-stat-val accent">{{ stats.pending }}</div>
      </el-card>
      <el-card shadow="never">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div class="voa-stat-label">{{ $t('dashboard.statApproved') }}</div>
          <el-icon style="font-size:22px;color:var(--el-color-success);opacity:.8"><CircleCheck /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.approved }}</div>
      </el-card>
      <el-card shadow="never">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div class="voa-stat-label">{{ $t('dashboard.statCancelled') }}</div>
          <el-icon style="font-size:22px;color:var(--el-color-danger);opacity:.8"><CircleClose /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.cancelled }}</div>
      </el-card>
    </div>

    <!-- Travel type metrics -->
    <div class="voa-stats-grid" style="margin-bottom:20px">
      <el-card shadow="never" v-for="type in travelTypeStats" :key="type.key">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div class="voa-stat-label">{{ $t('travelRequest.travelType_' + type.key) }}</div>
          <el-icon :style="{ fontSize: '22px', color: type.color, opacity: .85 }">
            <component :is="type.icon" />
          </el-icon>
        </div>
        <div class="voa-stat-val" :style="{ color: type.color }">{{ type.count }}</div>
      </el-card>
    </div>

    <!-- Two-column cards: pending + recent requests -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
      <!-- Pending approval -->
      <el-card shadow="never" v-loading="travelRequestStore.loading">
        <template #header>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
              <el-icon style="color:var(--el-color-warning)"><Warning /></el-icon>
              <span style="font-weight:700">{{ $t('dashboard.pendingApproval') }}</span>
            </div>
            <el-badge :value="stats.pending" type="warning" v-if="stats.pending > 0" />
          </div>
        </template>
        <el-empty
          v-if="pendingRequests.length === 0"
          :description="$t('dashboard.pendingEmpty')"
          :image-size="50"
        />
        <div
          v-for="req in pendingRequests.slice(0, 5)"
          :key="req.id"
          style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--el-border-color)"
        >
          <el-icon style="color:var(--el-text-color-secondary);flex-shrink:0">
            <component :is="travelTypeIcon(req.travel_type)" />
          </el-icon>
          <div style="flex:1;cursor:pointer" @click="$router.push('/requests/' + req.id)">
            <div style="display:flex;align-items:center;gap:6px">
              <span style="font-family:var(--voa-mono,monospace);font-size:11px;font-weight:700;color:var(--el-color-primary)">{{ formatRequestId(req.id) }}</span>
              <span style="font-weight:600;font-size:13.5px">{{ req.destination }}</span>
            </div>
            <div style="font-size:12px;color:var(--el-text-color-secondary)">
              {{ req.requester_name }} · {{ formatDate(req.departure_date) }}
            </div>
          </div>
          <el-button
            v-if="authStore.isApprover"
            size="small"
            type="success"
            @click.stop="handleApprove(req)"
            :loading="approvingId === req.id"
          >
            {{ $t('dashboard.approve') }}
          </el-button>
        </div>
      </el-card>

      <!-- Recent requests -->
      <el-card shadow="never" v-loading="travelRequestStore.loading">
        <template #header>
          <div style="display:flex;align-items:center;gap:8px">
            <el-icon style="color:var(--el-color-primary)"><Clock /></el-icon>
            <span style="font-weight:700">{{ $t('dashboard.recentRequests') }}</span>
          </div>
        </template>
        <el-empty
          v-if="recentRequests.length === 0"
          :description="$t('dashboard.recentEmpty')"
          :image-size="50"
        />
        <div
          v-for="req in recentRequests"
          :key="req.id"
          style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--el-border-color);cursor:pointer"
          @click="$router.push('/requests/' + req.id)"
        >
          <el-icon style="color:var(--el-text-color-secondary);flex-shrink:0">
            <component :is="travelTypeIcon(req.travel_type)" />
          </el-icon>
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px">
              <span style="font-family:var(--voa-mono,monospace);font-size:11px;font-weight:700;color:var(--el-color-primary)">{{ formatRequestId(req.id) }}</span>
              <span style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ req.destination }}
              </span>
            </div>
            <div style="font-size:12px;color:var(--el-text-color-secondary)">
              {{ req.requester_name }} · {{ formatDate(req.departure_date) }}
            </div>
          </div>
          <el-tag :type="getStatusType(req.status)" size="small" round>
            {{ translateStatus(req.status) }}
          </el-tag>
        </div>
      </el-card>
    </div>

    <!-- Two-column cards: top destinations + travel types most used -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <!-- Top 10 destinations -->
      <el-card shadow="never">
        <template #header>
          <div style="display:flex;align-items:center;gap:8px">
            <el-icon style="color:var(--el-color-primary)"><TrophyBase /></el-icon>
            <span style="font-weight:700">{{ $t('dashboard.topDestinations') }}</span>
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
          style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--el-border-color)"
        >
          <span
            :style="{
              width: '22px', height: '22px', borderRadius: '50%',
              background: i < 3 ? 'var(--el-color-primary)' : 'var(--el-fill-color)',
              color: i < 3 ? '#fff' : 'var(--el-text-color-secondary)',
              display: 'grid', placeItems: 'center',
              fontSize: '11px', fontWeight: 700, flexShrink: 0
            }"
          >{{ i + 1 }}</span>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              <el-icon style="font-size:11px;margin-right:3px;vertical-align:middle"><Location /></el-icon>
              {{ dest.name }}
            </div>
          </div>
          <el-tag type="info" size="small" round>{{ dest.count }}</el-tag>
        </div>
      </el-card>

      <!-- Travel types most used -->
      <el-card shadow="never">
        <template #header>
          <div style="display:flex;align-items:center;gap:8px">
            <el-icon style="color:var(--el-color-primary)"><Van /></el-icon>
            <span style="font-weight:700">{{ $t('dashboard.travelTypesMostUsed') }}</span>
          </div>
        </template>
        <el-empty
          v-if="travelTypeStats.every(t => t.count === 0)"
          :description="$t('dashboard.noTravelTypes')"
          :image-size="50"
        />
        <div
          v-for="type in sortedTravelTypes"
          :key="type.key"
          style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--el-border-color)"
        >
          <div
            :style="{
              width: '36px', height: '36px', borderRadius: '8px',
              background: type.color + '20',
              display: 'grid', placeItems: 'center', flexShrink: 0
            }"
          >
            <el-icon :style="{ fontSize: '18px', color: type.color }">
              <component :is="type.icon" />
            </el-icon>
          </div>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:600">{{ $t('travelRequest.travelType_' + type.key) }}</div>
            <div style="font-size:12px;color:var(--el-text-color-secondary)">
              {{ type.percentage }}% {{ $t('dashboard.ofTotalRequests') }}
            </div>
          </div>
          <div style="text-align:right">
            <div style="font-size:20px;font-weight:800;color:var(--el-text-color-primary)">{{ type.count }}</div>
            <div style="font-size:11px;color:var(--el-text-color-secondary)">{{ $t('dashboard.requests') }}</div>
          </div>
        </div>
      </el-card>
    </div>

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
import { ref, computed, onMounted } from 'vue'
import { useTravelRequestStore } from '@/stores/travelRequest'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useI18n } from 'vue-i18n'
import TravelRequestForm from '@/components/TravelRequestForm.vue'
import {
  Plus, DataLine, Clock, CircleCheck, CircleClose,
  Warning, Location, TrophyBase, List,
  Van, Promotion, MapLocation, House,
} from '@element-plus/icons-vue'

const { t } = useI18n()
const travelRequestStore = useTravelRequestStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const showCreateDialog = ref(false)
const approvingId = ref(null)

const requests = computed(() => travelRequestStore.travelRequests || [])

const stats = computed(() => ({
  total: requests.value.length,
  pending: requests.value.filter(r => r.status === 'requested').length,
  approved: requests.value.filter(r => r.status === 'approved').length,
  cancelled: requests.value.filter(r => r.status === 'cancelled').length,
}))

const travelTypeStats = computed(() => [
  { key: 'aereo',  icon: Promotion,    color: '#3b82f6', count: requests.value.filter(r => r.travel_type === 'aereo').length },
  { key: 'onibus', icon: Van,          color: '#f59e0b', count: requests.value.filter(r => r.travel_type === 'onibus').length },
  { key: 'carro',  icon: MapLocation,  color: '#8b5cf6', count: requests.value.filter(r => r.travel_type === 'carro').length },
  { key: 'hotel',  icon: House,        color: '#10b981', count: requests.value.filter(r => r.travel_type === 'hotel').length },
])

const sortedTravelTypes = computed(() => {
  const total = requests.value.length || 1
  return [...travelTypeStats.value]
    .sort((a, b) => b.count - a.count)
    .map(type => ({
      ...type,
      percentage: Math.round((type.count / total) * 100)
    }))
})

const topDestinations = computed(() => {
  const counts = {}
  requests.value.forEach(r => {
    if (r.destination) counts[r.destination] = (counts[r.destination] || 0) + 1
  })
  return Object.entries(counts)
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 10)
})

const pendingRequests = computed(() =>
  requests.value.filter(r => r.status === 'requested')
)

const recentRequests = computed(() =>
  [...requests.value]
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 8)
)

const travelTypeIcon = (type) => {
  return { aereo: Promotion, onibus: Van, carro: MapLocation, hotel: House }[type] || Location
}

const getStatusType = (status) => {
  const types = { requested: 'warning', approved: 'success', cancelled: 'danger' }
  return types[status] || ''
}

const translateStatus = (status) => ({
  requested: t('status.requested'),
  approved: t('status.approved'),
  cancelled: t('status.cancelled'),
}[status] || status)

const formatDate = (date) => {
  if (!date) return '-'
  const d = typeof date === 'string' && date.includes('T') ? date.split('T')[0] : date
  return new Date(d + 'T12:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}

// Formatar ID do pedido no formato VG-XXX
const formatRequestId = (id) => {
  if (!id) return '-'
  return `VG-${String(id).padStart(3, '0')}`
}

const handleApprove = async (req) => {
  approvingId.value = req.id
  await travelRequestStore.updateStatus(req.id, 'approved')
  approvingId.value = null
}

const handleCreate = async (data) => {
  const success = await travelRequestStore.createTravelRequest(data)
  if (success) showCreateDialog.value = false
}

onMounted(() => {
  themeStore.initTheme()
  travelRequestStore.fetchTravelRequests()
})
</script>

<style scoped>
@media (max-width: 768px) {
  div[style*="grid-template-columns:1fr 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
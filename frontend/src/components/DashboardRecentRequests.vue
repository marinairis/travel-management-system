<template>
  <el-card shadow="never" v-loading="isLoading">
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
      v-if="requests.length === 0"
      :description="$t('dashboard.recentEmpty')"
      :image-size="50"
    />
    <div
      v-for="req in requests"
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
</template>

<script setup>
import { Clock } from '@element-plus/icons-vue'
import { useDateFormat } from '@/composables/useDateFormat'
import { useTravelType } from '@/composables/useTravelType'
import { useRequestStatus } from '@/composables/useRequestStatus'

defineProps({
  requests: { type: Array, required: true },
  isLoading: { type: Boolean, default: false },
})

const { formatDate } = useDateFormat()
const { travelTypeIcon, formatRequestId } = useTravelType()
const { getStatusType, translateStatus } = useRequestStatus()
</script>

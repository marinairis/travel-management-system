<template>
  <el-card shadow="never" v-loading="isLoading">
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
      v-if="requests.length === 0"
      :description="$t('dashboard.pendingEmpty')"
      :image-size="50"
    />
    <div
      v-for="req in requests"
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
      <template v-if="isApprover && req.user_id !== currentUserId">
        <el-tooltip :content="$t('dashboard.approve')" placement="top">
          <el-icon
            style="color: var(--el-color-success); cursor: pointer; font-size: 18px"
            @click.stop="$emit('approve', req)"
          >
            <SuccessFilled />
          </el-icon>
        </el-tooltip>
        <el-tooltip :content="$t('common.cancel')" placement="top">
          <el-icon
            style="color: var(--el-color-danger); cursor: pointer; font-size: 18px"
            @click.stop="$emit('cancel', req)"
          >
            <CircleCloseFilled />
          </el-icon>
        </el-tooltip>
      </template>
      <el-tooltip v-else-if="isApprover && req.user_id === currentUserId" :content="$t('dashboard.cannotApproveOwn')" placement="top">
        <el-icon style="color: var(--el-text-color-secondary); cursor: help; font-size: 18px">
          <Lock />
        </el-icon>
      </el-tooltip>
    </div>
  </el-card>
</template>

<script setup>
import { Warning, Lock, SuccessFilled, CircleCloseFilled } from '@element-plus/icons-vue'
import { useDateFormat } from '@/composables/useDateFormat'
import { useTravelType } from '@/composables/useTravelType'

defineProps({
  requests: { type: Array, required: true },
  isLoading: { type: Boolean, default: false },
  isApprover: { type: Boolean, default: false },
  currentUserId: { type: Number, default: null },
})

defineEmits(['approve', 'cancel'])

const { formatDate } = useDateFormat()
const { travelTypeIcon, formatRequestId } = useTravelType()
</script>

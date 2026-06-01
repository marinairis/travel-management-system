<template>
  <div>
    <div class="voa-stats-grid">
      <el-card shadow="never">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px">
          <div class="voa-stat-label">{{ $t('dashboard.statTotal') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-primary); opacity: 0.7"><DataLine /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.total }}</div>
      </el-card>
      <el-card shadow="never">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px">
          <div class="voa-stat-label">{{ $t('dashboard.statPending') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-warning); opacity: 0.8"><Clock /></el-icon>
        </div>
        <div class="voa-stat-val accent">{{ stats.pending }}</div>
      </el-card>
      <el-card shadow="never">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px">
          <div class="voa-stat-label">{{ $t('dashboard.statApproved') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-success); opacity: 0.8"><CircleCheck /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.approved }}</div>
      </el-card>
      <el-card shadow="never">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px">
          <div class="voa-stat-label">{{ $t('dashboard.statCancelled') }}</div>
          <el-icon style="font-size: 22px; color: var(--el-color-danger); opacity: 0.8"><CircleClose /></el-icon>
        </div>
        <div class="voa-stat-val">{{ stats.cancelled }}</div>
      </el-card>
    </div>

    <div class="voa-stats-grid" style="margin-bottom: 20px">
      <el-card shadow="never" v-for="type in travelTypeStats" :key="type.key">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px">
          <div class="voa-stat-label">{{ $t('travelRequest.travelType_' + type.key) }}</div>
          <el-icon :style="{ fontSize: '22px', color: type.color, opacity: 0.85 }">
            <component :is="type.icon" />
          </el-icon>
        </div>
        <div class="voa-stat-val" :style="{ color: type.color }">{{ type.count }}</div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { DataLine, Clock, CircleCheck, CircleClose } from '@element-plus/icons-vue'

defineProps({
  stats: { type: Object, required: true },
  travelTypeStats: { type: Array, required: true },
})
</script>

<template>
  <el-dropdown trigger="click" @command="handleSwitch">
    <div class="demo-switcher-trigger">
      <span class="demo-switcher-label">{{ $t('demo.viewAs') }}</span>
      <el-tag :type="roleTagType" size="small" class="demo-switcher-role">{{ roleLabel }}</el-tag>
    </div>
    <template #dropdown>
      <el-dropdown-menu>
        <el-dropdown-item v-for="user in DEMO_USERS" :key="user.email" :command="user">
          <div class="demo-user-item">
            <el-avatar
              :size="28"
              :style="{
                background: avatarBg(user.email),
                color: '#fff',
                fontSize: '11px',
                fontWeight: 700,
                flexShrink: 0,
              }"
            >
              {{ initials(user.name) }}
            </el-avatar>
            <span class="demo-user-name">{{ user.name }}</span>
            <el-tag :type="getRoleTagType(user.role)" size="small">{{
              getRoleLabel(user.role)
            }}</el-tag>
          </div>
        </el-dropdown-item>
      </el-dropdown-menu>
    </template>
  </el-dropdown>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useAvatar } from '@/composables/useAvatar'
import { useRole } from '@/composables/useRole'
import { DEMO_USERS } from '@/constants/demoUsers'

const authStore = useAuthStore()
const { initials, avatarBg } = useAvatar()
const {
  currentUserRoleLabel: roleLabel,
  currentUserRoleTagType: roleTagType,
  getRoleLabel,
  getRoleTagType,
} = useRole()

const handleSwitch = async (user) => {
  if (user.email === authStore.user?.email) return
  authStore.token = null
  authStore.user = null
  await authStore.login({ email: user.email, password: user.password })
}
</script>

<style scoped>
.demo-switcher-trigger {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  padding: 4px 8px;
  border: 1px solid var(--el-border-color);
  border-radius: var(--el-border-radius-base);
  background: var(--el-bg-color);
  font-size: 13px;
  color: var(--el-text-color-regular);
  transition: border-color 0.2s;
  height: 28px;
  box-sizing: border-box;
}

.demo-switcher-trigger:hover {
  border-color: var(--el-color-primary);
}

.demo-user-item {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 200px;
}

.demo-user-name {
  flex: 1;
  font-size: 13px;
}
</style>

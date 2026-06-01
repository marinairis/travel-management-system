<template>
  <el-dialog
    :model-value="modelValue"
    :title="$t('users.inviteUser')"
    width="480px"
    destroy-on-close
    @update:model-value="$emit('update:modelValue', $event)"
    @closed="handleClosed"
  >
    <div v-if="inviteSent" class="voa-sent-center">
      <div class="voa-sent-icon">✓</div>
      <div style="font-size: 18px; font-weight: 700; margin-bottom: 6px">
        {{ $t('users.inviteSentTitle') }}
      </div>
      <div style="color: var(--el-text-color-secondary); font-size: 14px">
        {{ $t('users.inviteSentMsg') }}
      </div>
      <div style="font-weight: 700; margin-top: 4px">{{ form.email }}</div>
      <el-button style="margin-top: 18px" @click="reset">{{ $t('users.inviteAnother') }}</el-button>
    </div>

    <div v-else>
      <p style="color: var(--el-text-color-secondary); font-size: 13.5px; margin: -8px 0 16px">
        {{ $t('users.inviteSubtitle') }}
      </p>
      <div style="display: flex; flex-direction: column; gap: 13px">
        <div>
          <div
            style="
              font-size: 13px;
              font-weight: 500;
              margin-bottom: 5px;
              color: var(--el-text-color-regular);
            "
          >
            {{ $t('users.inviteEmail') }} <span style="color: var(--el-color-danger)">*</span>
          </div>
          <el-input v-model="form.email" type="email" :placeholder="$t('users.inviteEmailPh')" />
          <div
            v-if="errors.email"
            style="font-size: 12px; color: var(--el-color-danger); margin-top: 4px"
          >
            {{ errors.email }}
          </div>
        </div>
        <div>
          <div
            style="
              font-size: 13px;
              font-weight: 500;
              margin-bottom: 5px;
              color: var(--el-text-color-regular);
            "
          >
            {{ $t('users.inviteRoleSelect') }}
          </div>
          <el-select v-model="form.role" style="width: 100%">
            <el-option value="requester" :label="$t('users.roleRequester')" />
            <el-option value="manager" :label="$t('users.roleManager')" />
            <el-option value="admin" :label="$t('users.roleAdmin')" />
          </el-select>
        </div>
        <div class="voa-role-hint">
          <strong>{{ getRoleLabel(form.role) }}:</strong> {{ roleDesc(form.role) }}
        </div>
      </div>
    </div>

    <template v-if="!inviteSent" #footer>
      <el-button @click="$emit('update:modelValue', false)">{{ $t('common.cancel') }}</el-button>
      <el-button type="primary" @click="handleInvite" :loading="isLoading">
        {{ isLoading ? $t('users.inviting') : $t('users.inviteSend') }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useUserStore } from '@/stores/user'
import { useRole } from '@/composables/useRole'

defineProps({
  modelValue: { type: Boolean, required: true },
})

defineEmits(['update:modelValue'])

const { t } = useI18n()
const userStore = useUserStore()
const { getRoleLabel } = useRole()

const inviteSent = ref(false)
const isLoading = ref(false)
const errors = ref({})
const form = reactive({ email: '', role: 'requester' })

const roleDesc = (role) =>
  ({
    requester: t('users.roleDescRequester'),
    manager: t('users.roleDescManager'),
    admin: t('users.roleDescAdmin'),
  })[role] || ''

const reset = () => {
  form.email = ''
  form.role = 'requester'
  errors.value = {}
  inviteSent.value = false
}

const handleClosed = () => {
  reset()
}

const handleInvite = async () => {
  errors.value = {}
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(form.email)) {
    errors.value.email = t('auth.emailInvalid')
    return
  }
  isLoading.value = true
  const success = await userStore.inviteUser(form)
  isLoading.value = false
  if (success) inviteSent.value = true
}
</script>

<template>
  <el-dialog
    :model-value="modelValue"
    :title="$t('travelRequest.cancelTitle')"
    width="450px"
    align-center
    destroy-on-close
    @update:model-value="handleClose"
  >
    <p>{{ $t('travelRequest.cancelConfirmMessage') }}</p>
    <el-form-item :label="$t('travelRequest.cancelReasonLabel')" required style="margin-top: 16px">
      <el-input
        v-model="cancelReason"
        type="textarea"
        :rows="3"
        :placeholder="$t('travelRequest.cancelReasonPlaceholder')"
      />
    </el-form-item>
    <template #footer>
      <el-button @click="handleClose">{{ $t('common.cancel') }}</el-button>
      <el-button
        :type="confirmType"
        :loading="isLoading"
        :disabled="!cancelReason.trim()"
        @click="handleConfirm"
      >
        {{ $t('common.confirm') }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: { type: Boolean, required: true },
  isLoading: { type: Boolean, default: false },
  confirmType: { type: String, default: 'warning' },
})
const emit = defineEmits(['update:modelValue', 'confirm'])

const cancelReason = ref('')

const handleConfirm = () => emit('confirm', cancelReason.value)

const handleClose = () => {
  cancelReason.value = ''
  emit('update:modelValue', false)
}
</script>

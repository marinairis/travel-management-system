<template>
  <el-dialog
    :model-value="modelValue"
    :title="$t('users.confirmDelete')"
    width="400px"
    align-center
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <p>{{ $t('users.deleteUserConfirm') }}</p>
    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">{{ $t('common.cancel') }}</el-button>
      <el-button type="danger" @click="$emit('confirm')" :loading="isLoading">
        {{ $t('common.delete') }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, required: true },
  isLoading: { type: Boolean, default: false },
})

defineEmits(['update:modelValue', 'confirm'])
</script>

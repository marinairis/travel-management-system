<template>
  <el-dialog
    :model-value="modelValue"
    :title="$t('users.editUser')"
    width="500px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
      <el-form-item :label="$t('users.name')" prop="name">
        <el-input v-model="form.name" />
      </el-form-item>
      <el-form-item :label="$t('users.userType')" prop="role">
        <el-select v-model="form.role" style="width: 100%">
          <el-option value="requester" :label="$t('users.roleRequester')" />
          <el-option value="manager" :label="$t('users.roleManager')" />
          <el-option value="admin" :label="$t('users.roleAdmin')" />
        </el-select>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">{{ $t('common.cancel') }}</el-button>
      <el-button type="primary" @click="handleSubmit" :loading="isLoading">
        {{ $t('common.save') }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  initialName: { type: String, default: '' },
  initialRole: { type: String, default: 'requester' },
  isLoading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'submit'])

const { t } = useI18n()
const formRef = ref(null)
const form = reactive({ name: props.initialName, role: props.initialRole })

const rules = {
  name: [{ required: true, message: t('users.nameRequired'), trigger: 'blur' }],
  role: [{ required: true, message: t('users.roleRequired'), trigger: 'change' }],
}

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      form.name = props.initialName
      form.role = props.initialRole
    }
  },
)

const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (valid) emit('submit', { name: form.name, role: form.role })
  })
}
</script>

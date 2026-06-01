import { useI18n } from 'vue-i18n'

export function usePasswordValidation() {
  const { t } = useI18n()

  const passwordComplexityRule = {
    validator: (rule, value, callback) => {
      if (!/[A-Z]/.test(value) || !/[0-9]/.test(value) || !/[^A-Za-z0-9]/.test(value)) {
        callback(new Error(t('auth.passwordComplexity')))
      } else {
        callback()
      }
    },
    trigger: 'blur',
  }

  const passwordMatchRule = (formData) => ({
    validator: (rule, value, callback) => {
      if (value !== formData.password) {
        callback(new Error(t('auth.passwordsDoNotMatch')))
      } else {
        callback()
      }
    },
    trigger: 'blur',
  })

  return { passwordComplexityRule, passwordMatchRule }
}

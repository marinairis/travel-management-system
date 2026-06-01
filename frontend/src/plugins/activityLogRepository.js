import api from './axios'

const buildParams = ({ userId, action, modelType, perPage, page }) => {
  const params = new URLSearchParams()
  if (userId) params.append('user_id', userId)
  if (action) params.append('action', action)
  if (modelType) params.append('model_type', modelType)
  params.append('per_page', perPage || 10)
  params.append('page', page || 1)
  return params
}

export const fetchAll = (filters = {}) => api.get(`/activity-logs?${buildParams(filters)}`)
export const fetchForModel = ({ modelId, modelType, perPage }) =>
  api.get('/activity-logs', { params: { model_id: modelId, model_type: modelType, per_page: perPage } })

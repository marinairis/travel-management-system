import api from './axios'

const buildParams = ({ userType, status, email, perPage, page }) => {
  const params = new URLSearchParams()
  if (userType) params.append('user_type', userType)
  if (status) params.append('status', status)
  if (email) params.append('email', email)
  params.append('per_page', perPage || 10)
  params.append('page', page || 1)
  return params
}

export const fetchAll = (query) => api.get(`/users?${buildParams(query)}`)
export const update = (id, data) => api.put(`/users/${id}`, data)
export const remove = (id) => api.delete(`/users/${id}`)
export const fetchBasic = () => api.get('/users/basic')
export const invite = (data) => api.post('/users/invite', data)
export const toggleStatus = (id) => api.patch(`/users/${id}/toggle-status`)
export const getPendingRequestsCount = (userId) =>
  api.get(`/users/${userId}/pending-requests-count`)
export const resendInvitation = (id) => api.post(`/invitations/${id}/resend`)

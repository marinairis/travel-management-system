import api from './axios'

const buildParams = ({ status, destination, start_date, end_date, per_page, page }) => {
  const params = new URLSearchParams()
  if (status) params.append('status', status)
  if (destination) params.append('destination', destination)
  if (start_date) params.append('start_date', start_date)
  if (end_date) params.append('end_date', end_date)
  params.append('per_page', per_page || 10)
  params.append('page', page || 1)
  return params
}

export const fetchAll = (filters = {}) => api.get(`/travel-requests?${buildParams(filters)}`)
export const getById = (id) => api.get(`/travel-requests/${id}`)
export const create = (data) => api.post('/travel-requests', data)
export const update = (id, data) => api.put(`/travel-requests/${id}`, data)
export const updateStatus = (id, status) => api.patch(`/travel-requests/${id}/status`, { status })
export const cancel = (id, reason) => api.patch(`/travel-requests/${id}/cancel`, { reason })
export const deleteRequest = (id) => api.delete(`/travel-requests/${id}`)

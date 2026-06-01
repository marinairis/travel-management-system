import api from './axios'

export const fetchByToken = (token) => api.get(`/invitations/${token}`)
export const accept = (token, data) => api.post(`/invitations/${token}/accept`, data)

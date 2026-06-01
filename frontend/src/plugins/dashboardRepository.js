import api from './axios'

export const fetchStats = () => api.get('/dashboard/stats')
export const fetchPendingApproval = () => api.get('/dashboard/pending-approval')
export const fetchRecentRequests = () => api.get('/dashboard/recent-requests')

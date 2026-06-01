import api from './axios'

export const login = (credentials) => api.post('/login', credentials)
export const register = (userData) => api.post('/register', userData)
export const logout = () => api.post('/logout')
export const fetchCurrentUser = () => api.get('/me')
export const forgotPassword = (email) => api.post('/forgot-password', { email })
export const resetPassword = (data) => api.post('/reset-password', data)

import api from './axios'

export const fetchDestinations = () =>
  api.get('/locations/destinations', { skipGlobalErrorHandler: true })

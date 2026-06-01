import { defineStore } from 'pinia'
import * as destinationRepository from '@/plugins/destinationRepository'

export const useDestinationsStore = defineStore('destinations', {
  state: () => ({
    destinations: [],
    isLoading: false,
    error: null,
    lastFetch: null,
    cacheTimeout: 30 * 60 * 1000,
  }),

  getters: {
    isCacheValid: (state) => {
      if (!state.lastFetch) return false
      return Date.now() - state.lastFetch < state.cacheTimeout
    },

    getDestinationsForSelect: (state) => {
      return state.destinations.map((dest) => ({
        value: dest.value,
        label: dest.label,
        id: dest.id,
        nome: dest.nome,
        estado: dest.estado,
        uf: dest.uf,
      }))
    },

    searchDestinations: (state) => (query) => {
      if (!query) return state.destinations
      const searchTerm = query.toLowerCase()
      return state.destinations.filter(
        (dest) =>
          dest.label.toLowerCase().includes(searchTerm) ||
          dest.nome.toLowerCase().includes(searchTerm) ||
          dest.estado.toLowerCase().includes(searchTerm) ||
          dest.uf.toLowerCase().includes(searchTerm),
      )
    },
  },

  actions: {
    async fetchDestinations(forceRefresh = false) {
      if (this.isCacheValid && !forceRefresh && this.destinations.length > 0) {
        return this.destinations
      }

      this.isLoading = true
      this.error = null

      try {
        const response = await destinationRepository.fetchDestinations()
        if (response.data.success) {
          this.destinations = response.data.data
          this.lastFetch = Date.now()
          this.error = null
        } else {
          throw new Error(response.data.message || 'Failed to fetch destinations')
        }
      } catch (error) {
        console.error(error)
        this.error = error.message || 'Failed to load destinations'
        throw error
      } finally {
        this.isLoading = false
      }

      return this.destinations
    },

    async getDestinations() {
      if (this.isCacheValid && this.destinations.length > 0) {
        return this.destinations
      }
      return await this.fetchDestinations()
    },

    clearCache() {
      this.destinations = []
      this.lastFetch = null
      this.error = null
    },

    refreshDestinations() {
      return this.fetchDestinations(true)
    },
  },
})

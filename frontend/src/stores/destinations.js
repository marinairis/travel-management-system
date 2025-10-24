import { defineStore } from 'pinia'
import api from '@/plugins/axios'

export const useDestinationsStore = defineStore('destinations', {
  state: () => ({
    destinations: [],
    loading: false,
    error: null,
    lastFetch: null,
    cacheTimeout: 30 * 60 * 1000, // 30 minutos
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
      // Se já temos dados válidos no cache e não é refresh forçado, não busca novamente
      if (this.isCacheValid && !forceRefresh && this.destinations.length > 0) {
        return this.destinations
      }

      this.loading = true
      this.error = null

      try {
        const response = await api.get('/locations/destinations')

        if (response.data.success) {
          this.destinations = response.data.data
          this.lastFetch = Date.now()
          this.error = null
        } else {
          throw new Error(response.data.message || 'Erro ao buscar destinos')
        }
      } catch (error) {
        console.error('Erro ao buscar destinos:', error)
        this.error = error.message || 'Erro ao carregar destinos'
        throw error
      } finally {
        this.loading = false
      }

      return this.destinations
    },

    async getDestinations() {
      // Se já temos dados válidos no cache, retorna imediatamente
      if (this.isCacheValid && this.destinations.length > 0) {
        return this.destinations
      }

      // Caso contrário, busca os dados
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

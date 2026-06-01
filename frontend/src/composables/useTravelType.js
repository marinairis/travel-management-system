import { House, Location, MapLocation, Promotion, Van } from '@element-plus/icons-vue'

export function useTravelType() {
  const travelTypeIcon = (type) =>
    ({ plane: Promotion, bus: Van, car: MapLocation, hotel: House }[type] || Location)

  const getTravelTypeColor = (type) =>
    ({
      plane: 'var(--travel-type-plane)',
      bus: 'var(--travel-type-bus)',
      car: 'var(--travel-type-car)',
      hotel: 'var(--travel-type-hotel)',
    }[type] || 'var(--el-color-primary)')

  const formatRequestId = (id) => {
    if (!id) return '-'
    return `VOA-${String(id).padStart(4, '0')}`
  }

  return { travelTypeIcon, getTravelTypeColor, formatRequestId }
}

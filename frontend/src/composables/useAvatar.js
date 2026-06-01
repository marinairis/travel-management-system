export function useAvatar() {
  const initials = (name) => {
    const parts = String(name || '')
      .trim()
      .split(' ')
    return ((parts[0]?.[0] || '') + (parts[parts.length - 1]?.[0] || '')).toUpperCase()
  }

  const avatarBg = (id) => {
    const colors = [
      'var(--avatar-color-1)',
      'var(--avatar-color-2)',
      'var(--avatar-color-3)',
      'var(--avatar-color-4)',
      'var(--avatar-color-5)',
      'var(--avatar-color-6)',
    ]
    let hashValue = 0
    const idString = String(id ?? '')
    for (let i = 0; i < idString.length; i++)
      hashValue = idString.charCodeAt(i) + ((hashValue << 5) - hashValue)
    return colors[Math.abs(hashValue) % colors.length]
  }

  return { initials, avatarBg }
}

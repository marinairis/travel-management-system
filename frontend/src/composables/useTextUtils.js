import { ref } from 'vue'

export function useTextUtils() {
  const isTextOverflowing = (text, maxWidth = 300, styles = {}) => {
    if (!text) return false

    const tempElement = document.createElement('span')
    tempElement.style.visibility = 'hidden'
    tempElement.style.position = 'absolute'
    tempElement.style.whiteSpace = 'nowrap'
    tempElement.style.fontSize = '14px'
    tempElement.style.fontFamily = 'inherit'
    tempElement.textContent = text

    Object.assign(tempElement.style, styles)

    document.body.appendChild(tempElement)
    const textWidth = tempElement.offsetWidth
    document.body.removeChild(tempElement)

    return textWidth > maxWidth
  }

  const useTextOverflowCheck = (text, maxWidth = 300, styles = {}) => {
    const isOverflowing = ref(false)

    const checkOverflow = () => {
      isOverflowing.value = isTextOverflowing(text, maxWidth, styles)
    }

    checkOverflow()

    return {
      isOverflowing,
      checkOverflow,
    }
  }

  const truncateText = (text, maxLength = 50, suffix = '...') => {
    if (!text || text.length <= maxLength) return text
    return text.substring(0, maxLength) + suffix
  }

  const shouldTruncate = (text, maxLength = 50) => {
    return text && text.length > maxLength
  }

  return {
    isTextOverflowing,
    useTextOverflowCheck,
    truncateText,
    shouldTruncate,
  }
}

import { ref } from 'vue'

/**
 * Composable para utilitários de texto
 */
export function useTextUtils() {
  /**
   * Verifica se um texto está excedendo a largura disponível
   * @param {string} text - O texto a ser verificado
   * @param {number} maxWidth - Largura máxima em pixels (padrão: 300)
   * @param {object} styles - Estilos adicionais para aplicar na medição
   * @returns {boolean} - true se o texto exceder a largura
   */
  const isTextOverflowing = (text, maxWidth = 300, styles = {}) => {
    if (!text) return false

    // Criar um elemento temporário para medir o texto
    const tempElement = document.createElement('span')
    tempElement.style.visibility = 'hidden'
    tempElement.style.position = 'absolute'
    tempElement.style.whiteSpace = 'nowrap'
    tempElement.style.fontSize = '14px' // Tamanho padrão
    tempElement.style.fontFamily = 'inherit'
    tempElement.textContent = text

    // Aplicar estilos adicionais se fornecidos
    Object.assign(tempElement.style, styles)

    document.body.appendChild(tempElement)
    const textWidth = tempElement.offsetWidth
    document.body.removeChild(tempElement)

    return textWidth > maxWidth
  }

  /**
   * Verifica se um texto está excedendo a largura disponível de forma reativa
   * @param {string} text - O texto a ser verificado
   * @param {number} maxWidth - Largura máxima em pixels
   * @param {object} styles - Estilos adicionais para aplicar na medição
   * @returns {import('vue').Ref<boolean>} - Ref reativo com o resultado
   */
  const useTextOverflowCheck = (text, maxWidth = 300, styles = {}) => {
    const isOverflowing = ref(false)

    const checkOverflow = () => {
      isOverflowing.value = isTextOverflowing(text, maxWidth, styles)
    }

    // Verificar inicialmente
    checkOverflow()

    return {
      isOverflowing,
      checkOverflow,
    }
  }

  /**
   * Trunca um texto se exceder o comprimento máximo
   * @param {string} text - O texto a ser truncado
   * @param {number} maxLength - Comprimento máximo (padrão: 50)
   * @param {string} suffix - Sufixo para texto truncado (padrão: '...')
   * @returns {string} - Texto truncado se necessário
   */
  const truncateText = (text, maxLength = 50, suffix = '...') => {
    if (!text || text.length <= maxLength) return text
    return text.substring(0, maxLength) + suffix
  }

  /**
   * Verifica se um texto precisa ser truncado baseado no comprimento
   * @param {string} text - O texto a ser verificado
   * @param {number} maxLength - Comprimento máximo
   * @returns {boolean} - true se o texto precisa ser truncado
   */
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

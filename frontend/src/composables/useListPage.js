import { ref } from 'vue'

export function useListPage(route) {
  const currentPage = ref(Number(route.query.page) || 1)
  const pageSize = ref(Number(route.query.per_page) || 10)

  const paginationParams = () => ({ page: currentPage.value, per_page: pageSize.value })

  const resetPage = () => {
    currentPage.value = 1
  }

  return { currentPage, pageSize, paginationParams, resetPage }
}

# Frontend — Convenções Vue 3 / Pinia / Element Plus

Leia [`../docs/dev-skills.md#frontend`](../docs/dev-skills.md) para referência completa.
Para termos de domínio, consulte [`../docs/language-dictionary.md`](../docs/language-dictionary.md).

---

## Arquitetura

```
View (*View.vue) → Store (Pinia) → plugins/axios.js → API Backend
       ↑
  Component (reutilizável)
       ↑
  Composable (use*)
```

| Camada | Responsabilidade | Localização |
|---|---|---|
| **View** | Página completa, uma por rota. Orquestra stores e componentes. | `src/views/` |
| **Component** | Bloco de UI reutilizável. Sem chamadas HTTP diretas. | `src/components/` |
| **Store** | Estado e ações de um domínio. Toda chamada HTTP passa aqui. | `src/stores/` |
| **Composable** | Lógica reutilizável entre componentes. | `src/composables/` |
| **axios plugin** | HTTP client com interceptors de auth e erro. | `src/plugins/axios.js` |

---

## Nomenclatura

| Tipo | Padrão | Exemplo |
|---|---|---|
| View | `*View.vue` (PascalCase) | `TravelRequestsView.vue` |
| Component | PascalCase sem sufixo | `TravelRequestForm.vue` |
| Composable | `use*` (camelCase) | `useTextUtils.js` |
| Store | camelCase por domínio | `travelRequest.js` |

---

## Script Style

Sempre `<script setup>`. Nunca Options API.

```vue
<script setup>
import { ref, computed } from 'vue'
import { useTravelRequestStore } from '@/stores/travelRequest'

const store = useTravelRequestStore()
</script>
```

---

## Clean Code

- Sem comentários no código — nomes devem ser auto-explicativos.
- Props sempre tipadas com `defineProps`.
- Emits declarados explicitamente com `defineEmits`.
- Nomes descritivos: prefira `isLoadingRequests` a `loading`.

```vue
<script setup>
const props = defineProps({
  requestId: { type: Number, required: true },
  isEditable: { type: Boolean, default: false },
})
const emit = defineEmits(['update', 'cancel'])
</script>
```

---

## DRY / YAGNI

- Lógica reutilizada entre componentes vai em `src/composables/`.
- Nunca duplique chamadas axios fora das stores.
- Sem componentes genéricos especulativos — só abstraia ao segundo uso real.

---

## Limites de Tamanho

| Unidade | Limite |
|---|---|
| Responsabilidade por componente | 1 |
| Linhas de template por componente | máximo 200 |

Ao ultrapassar 200 linhas de template, quebre em subcomponentes.

---

## Stores (Pinia)

- Uma store por domínio de negócio.
- Estrutura padrão: `state`, `getters`, `actions`, `persist` (quando aplicável).
- Sem lógica de negócio nos componentes ou views — tudo nas actions da store.
- Persistência via `pinia-plugin-persistedstate` apenas para dados que precisam sobreviver ao reload (token, locale, theme).

```js
export const useTravelRequestStore = defineStore('travelRequest', {
  state: () => ({ requests: [], isLoading: false }),
  getters: {
    pendingRequests: (state) => state.requests.filter((r) => r.status === 'pending'),
  },
  actions: {
    async fetchRequests(filters) {
      this.isLoading = true
      try {
        const { data } = await axios.get('/travel-requests', { params: filters })
        this.requests = data.data
      } finally {
        this.isLoading = false
      }
    },
  },
})
```

---

## HTTP

- Todas as chamadas HTTP passam pelas actions das stores.
- Nunca chame `axios` diretamente em componentes ou views.
- O `plugins/axios.js` gerencia: Bearer token, erros 401/403/404/422/500 com `ElMessage`.

---

## Formulários

- Valide localmente (Element Plus `el-form` + rules) antes de chamar a store action.
- Exiba os erros de `422` por campo — o backend retorna `errors` com a estrutura do Laravel.
- Um componente de formulário por operação — Create e Edit são componentes separados ou modos distintos explicitamente controlados.

---

## i18n

- Todo texto visível ao usuário via `$t('chave')` no template ou `t('chave')` no script.
- Sem strings hardcoded em templates.
- Três locales: `pt-BR` (padrão), `en`, `es` em `src/i18n/locales/`.
- Ao adicionar funcionalidade, adicione as chaves nos três arquivos de tradução.

```vue
<template>
  <el-button>{{ $t('travelRequest.create') }}</el-button>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
const label = t('travelRequest.status.pending')
</script>
```

---

## Segurança

- Sem `v-html` com dados vindos do usuário ou da API — risco de XSS.
- Tokens de autenticação gerenciados exclusivamente pela store `auth` — sem acesso direto ao `localStorage` nos componentes.
- Inputs sensíveis (senhas) nunca persistidos no estado da store.
- Rotas protegidas declaradas via meta `requiresAuth`, `requiresAdmin` no router — não via condicionais nos componentes.

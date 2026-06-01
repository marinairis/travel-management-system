# Dev Skills — Convenções de Desenvolvimento

---

## Backend (Laravel 12 / PHP 8.3)

### 1. Arquitetura

```
Request → Controller (invokable) → Service (regra de negócio) → Repository (consulta DB)
                                                             ↑
                                                       Interface (contrato)
```

| Camada | Responsabilidade | Localização |
|---|---|---|
| **Controller** | Recebe a request, chama o Service, retorna Resource ou ErrorResource. Sem lógica de negócio. | `app/Http/Controllers/API/` |
| **Service** | Toda a regra de negócio. Depende de interfaces de Repository. | `app/Services/` |
| **Repository** | Apenas consultas ao banco. Implementa a interface do domínio. | `app/Repositories/` |
| **Interface** | Contrato entre camadas. | `app/Interfaces/Repositories/` e `app/Interfaces/Services/` |

- Controllers são **invokables** — use `__invoke()` para controllers de ação única.
- Injete interfaces, nunca implementações concretas.

---

### 2. Clean Code

- Sem comentários no código — os nomes devem ser auto-explicativos.
- `declare(strict_types=1)` em todos os arquivos PHP.
- Tipagem obrigatória em todos os métodos e propriedades.
- Nomes descritivos: prefira `getUserActiveRequests()` a `getData()`.
- Evite abreviações desnecessárias.

---

### 3. DRY — Don't Repeat Yourself

Abstraia qualquer lógica duplicada em métodos, classes ou traits compartilhadas. Se o mesmo bloco aparece em dois lugares, ele pertence a um Service, Helper ou Trait.

---

### 4. YAGNI — You Aren't Gonna Need It

Implemente apenas o que foi solicitado. Sem "pode ser útil no futuro", sem configurabilidade especulativa, sem abstrações prematuras.

---

### 5. Limites de Tamanho

| Unidade | Limite |
|---|---|
| Parâmetros por método | máximo 3 |
| Linhas por classe | máximo 100 |

Quando uma classe ultrapassar 100 linhas, quebre-a em classes menores com responsabilidade única.

---

### 6. SOLID

| Princípio | Aplicação no projeto |
|---|---|
| **S** — Single Responsibility | Controller orquestra, Service contém regras, Repository acessa o banco |
| **O** — Open/Closed | Use herança e interfaces em vez de modificar classes existentes |
| **L** — Liskov Substitution | Implementações de interface devem ser substituíveis sem quebrar o contrato |
| **I** — Interface Segregation | Interfaces granulares por domínio em `app/Interfaces/` |
| **D** — Dependency Inversion | Injete interfaces, não implementações concretas |

---

### 7. FormRequests

- Todo FormRequest deve usar a trait `FailedValidationJson` (`app/Http/Traits/FailedValidationJson.php`).
- Retorno de validação sempre em JSON com status `422`.
- Métodos `rules()`, `messages()` e `authorize()` sempre tipados.
- Um Request por operação — nunca reutilize um Request de Create para Update.

```php
use App\Http\Traits\FailedValidationJson;

class CreateTravelRequestFormRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool { return true; }
    public function rules(): array { return [...]; }
    public function messages(): array { return [...]; }
}
```

---

### 8. Log

**Dois níveis de log:**

**Aplicação** — erros de servidor, exceções, integrações externas:
```php
use Illuminate\Support\Facades\Log;

Log::info('mensagem', ['context' => $data]);
Log::error('mensagem', ['exception' => $e->getMessage()]);
```
Arquivo: `storage/logs/laravel.log`

**Atividade de negócio** — ações do usuário rastreáveis (tabela `activity_logs`):
```php
// Use a trait HasActivityLogging no Service ou Model

$this->logActivityCreate();                          // após criar
$this->logActivityUpdate($oldData);                 // após atualizar
$this->logActivityDelete();                         // antes de deletar
$this->logActivityStatusChange($old, $new);         // mudança de status
$this->logActivity('custom_action', $old, $new);    // ação customizada
```
Campos registrados: `user_id`, `action`, `model_type`, `model_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`.

Não use `dd()` ou `dump()` em produção.

---

### 9. Exceptions Personalizadas

Crie uma exception por domínio em `app/Exceptions/<Domain>/`. Cada exception deve:
- Herdar de `Exception`.
- Ter constantes com as mensagens de erro.
- Implementar `getStatusCode()`.

```php
class TravelRequestException extends Exception
{
    public const NOT_FOUND = 'Pedido de viagem não encontrado.';
    public const UNAUTHORIZED = 'Sem permissão para esta ação.';

    public function getStatusCode(): int
    {
        return $this->code ?: Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
```

---

### 10. Enums

Verifique `app/Enums/` antes de criar um novo. Use Enums para qualquer valor fixo. Sem números mágicos no código.

```php
// Errado
if ($status === 2) { ... }

// Correto
if ($status === TravelRequestStatus::APPROVED) { ... }
```

---

### 11. Sem Annotations nos Controllers

Não utilize annotations PHP (`#[Attribute]`) nem docblocks como lógica de negócio nos controllers. Registre middlewares, bindings e rotas via código explícito.

---

### 15. Swagger / OpenAPI

Anotações `@OA\*` ficam em `app/Docs/`, **nunca nos controllers**. Cada arquivo cobre um domínio:

| Arquivo | Domínio |
|---|---|
| `app/Docs/AuthDoc.php` | Auth |
| `app/Docs/TravelRequestDoc.php` | Travel Requests |
| `app/Docs/UserDoc.php` | Users |
| `app/Docs/DashboardDoc.php` | Dashboard |
| `app/Docs/InvitationDoc.php` | Invitations |
| `app/Docs/LocationDoc.php` | Locations |
| `app/Docs/ActivityLogDoc.php` | Activity Logs |
| `app/Docs/NotificationDoc.php` | Notifications |

**Regra obrigatória:** qualquer alteração em um controller (novo parâmetro, response code, rota) ou criação de um novo controller exige atualização/criação do Doc file correspondente. Regenerar com `php artisan l5-swagger:generate`.

---

### 12. Verbos HTTP

| Verbo | Uso |
|---|---|
| `POST` | Criar registro, enviar dados, validar algo |
| `PUT` | Atualizar registro completo |
| `PATCH` | Atualizar campo ou coluna específica |
| `GET` | Listagem, relatórios, CRON |
| `DELETE` | Remover registro |

---

### 13. Status Codes

| Código | Situação |
|---|---|
| `200` | Sucesso padrão |
| `201` | Criação com sucesso |
| `204` | Sucesso sem conteúdo |
| `400` | Erro genérico — capturado no `catch` do Controller |
| `404` | Página ou registro não encontrado |
| `422` | Erro de validação do Request (retornado automaticamente pelo Laravel) |
| `500` | Erro de servidor |

---

### 14. Segurança

- Sem XSS: nunca renderize HTML a partir de input do usuário sem sanitização.
- Sem SQL Injection: use Eloquent ou query builder parametrizado — nunca interpolação direta.
- Sem CSRF em rotas de API (JWT já protege).
- Validação obrigatória de todos os inputs via FormRequest antes de chegar ao Service.
- Sem exposição de stack trace ou dados sensíveis em respostas de erro.

---

---

## Frontend (Vue 3 / Pinia / Element Plus)

### 1. Arquitetura

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

### 2. Nomenclatura

| Tipo | Padrão | Exemplo |
|---|---|---|
| View | `*View.vue` (PascalCase) | `TravelRequestsView.vue` |
| Component | PascalCase sem sufixo | `TravelRequestForm.vue` |
| Composable | `use*` (camelCase) | `useTextUtils.js` |
| Store | camelCase por domínio | `travelRequest.js` |
| Arquivo de tradução | kebab-case | `pt-BR.json` |

---

### 3. Script Style

Sempre use `<script setup>`. Nunca use Options API.

```vue
<script setup>
import { ref, computed } from 'vue'
import { useTravelRequestStore } from '@/stores/travelRequest'

const store = useTravelRequestStore()
</script>
```

---

### 4. Clean Code

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

### 5. DRY

- Lógica reutilizada entre componentes vai em `src/composables/`.
- Nunca duplique chamadas axios fora das stores.
- Componentes repetidos extraem-se para `src/components/`.

---

### 6. YAGNI

Sem componentes genéricos especulativos. Sem abstrações antes de existir o segundo uso real.

---

### 7. Limites de Tamanho

| Unidade | Limite |
|---|---|
| Responsabilidade por componente | 1 |
| Linhas de template por componente | máximo 200 |

Ao ultrapassar 200 linhas de template, quebre em subcomponentes com responsabilidade única.

---

### 8. Stores (Pinia)

- Uma store por domínio de negócio.
- Estrutura padrão: `state`, `getters`, `actions`, `persist` (quando aplicável).
- Sem lógica de negócio nos componentes ou views — tudo nas actions da store.
- Persistência via `pinia-plugin-persistedstate` apenas para dados que precisam sobreviver ao reload (auth token, locale, theme).

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

### 9. HTTP

- Todas as chamadas HTTP passam pelas actions das stores.
- Nunca chame `axios` diretamente em componentes ou views.
- O `plugins/axios.js` gerencia: Bearer token, erros 401/403/404/422/500 com `ElMessage`.

---

### 10. Formulários

- Valide localmente (Element Plus `el-form` + rules) antes de chamar a store action.
- Exiba os erros de `422` por campo — o backend retorna `errors` com a estrutura do Laravel.
- Um componente de formulário por operação (Create e Edit são componentes separados ou modos distintos explicitamente controlados).

---

### 11. i18n

- Todo texto visível ao usuário via `$t('chave')` no template ou `t('chave')` no `<script setup>`.
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

### 12. Segurança

- Sem `v-html` com dados vindos do usuário ou da API — risco de XSS.
- Tokens de autenticação gerenciados exclusivamente pela store `auth` — sem acesso direto ao `localStorage` nos componentes.
- Inputs sensíveis (senhas) nunca persistidos no estado da store.
- Rotas protegidas declaradas via meta `requiresAuth`, `requiresAdmin` no router — não via condicionais nos componentes.

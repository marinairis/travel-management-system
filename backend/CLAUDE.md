# Backend — Convenções Laravel 12 / PHP 8.3

Leia [`../docs/dev-skills.md#backend`](../docs/dev-skills.md) para referência completa.
Para termos de domínio, consulte [`../docs/language-dictionary.md`](../docs/language-dictionary.md).

---

## Arquitetura

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

## Clean Code

- Sem comentários no código — os nomes devem ser auto-explicativos.
- `declare(strict_types=1)` em todos os arquivos PHP.
- Tipagem obrigatória em todos os métodos e propriedades.
- Nomes descritivos: prefira `getUserActiveRequests()` a `getData()`.
- Evite abreviações desnecessárias.

---

## DRY / YAGNI

- Abstraia lógica duplicada em Services, Helpers ou Traits — se aparece em dois lugares, pertence a uma classe compartilhada.
- Implemente apenas o que foi solicitado. Sem abstrações prematuras.

---

## Limites de Tamanho

| Unidade | Limite |
|---|---|
| Parâmetros por método | máximo 3 |
| Linhas por classe | máximo 100 |

---

## SOLID

| Princípio | Aplicação |
|---|---|
| **S** | Controller orquestra, Service contém regras, Repository acessa o banco |
| **O** | Use herança e interfaces em vez de modificar classes existentes |
| **L** | Implementações de interface substituíveis sem quebrar o contrato |
| **I** | Interfaces granulares por domínio em `app/Interfaces/` |
| **D** | Injete interfaces, não implementações concretas |

---

## FormRequests

- Sempre use a trait `FailedValidationJson` (`app/Http/Traits/FailedValidationJson.php`).
- Retorno de validação em JSON com status `422`.
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

## Log

**Aplicação** — erros de servidor, exceções, integrações externas:
```php
use Illuminate\Support\Facades\Log;

Log::info('mensagem', ['context' => $data]);
Log::error('mensagem', ['exception' => $e->getMessage()]);
```
Arquivo: `storage/logs/laravel.log`

**Atividade de negócio** — tabela MySQL `activity_logs` via trait `HasActivityLogging`:

| Método | Quando usar |
|---|---|
| `$this->logActivityCreate()` | Após criar um registro |
| `$this->logActivityUpdate($oldData)` | Após atualizar |
| `$this->logActivityDelete()` | Antes de deletar |
| `$this->logActivityStatusChange($old, $new)` | Mudança de status |
| `$this->logActivity($action, $old, $new)` | Ação customizada |

Campos registrados: `user_id`, `action`, `model_type`, `model_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`.

Não use `dd()` ou `dump()` em produção.

---

## Exceptions Personalizadas

Crie por domínio em `app/Exceptions/<Domain>/`. Cada exception deve herdar de `Exception`, ter constantes de mensagem e implementar `getStatusCode()`.

```php
class TravelRequestException extends Exception
{
    public const NOT_FOUND = 'Pedido de viagem não encontrado.';

    public function getStatusCode(): int
    {
        return $this->code ?: Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
```

---

## Enums

Verifique `app/Enums/` antes de criar um novo. Sem números mágicos no código.

```php
// Errado
if ($status === 2) { ... }

// Correto
if ($status === TravelRequestStatus::APPROVED) { ... }
```

---

## Sem Annotations

Não use `#[Attribute]` nem docblocks como lógica de negócio. Registre middlewares, bindings e rotas via código explícito.

---

## Verbos HTTP

| Verbo | Uso |
|---|---|
| `POST` | Criar registro, enviar dados, validar algo |
| `PUT` | Atualizar registro completo |
| `PATCH` | Atualizar campo ou coluna específica |
| `GET` | Listagem, relatórios, CRON |
| `DELETE` | Remover registro |

---

## Status Codes

| Código | Situação |
|---|---|
| `200` | Sucesso padrão |
| `201` | Criação com sucesso |
| `204` | Sucesso sem conteúdo |
| `400` | Erro genérico — capturado no `catch` do Controller |
| `404` | Registro não encontrado |
| `422` | Erro de validação do Request |
| `500` | Erro de servidor |

---

## Segurança

- Sem XSS: nunca renderize HTML de input do usuário sem sanitização.
- Sem SQL Injection: use Eloquent ou query builder parametrizado.
- Validação obrigatória via FormRequest antes de chegar ao Service.
- Sem exposição de stack trace ou dados sensíveis em respostas de erro.

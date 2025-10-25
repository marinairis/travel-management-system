# Sistema de Internacionalização (i18n) - Backend

Este documento descreve como o sistema de internacionalização foi implementado no backend Laravel.

## Estrutura

### Arquivos de Tradução

-   `resources/lang/pt-BR/messages.php` - Mensagens em português brasileiro
-   `resources/lang/en-US/messages.php` - Mensagens em inglês americano
-   `resources/lang/pt-BR/validation.php` - Mensagens de validação em português
-   `resources/lang/en-US/validation.php` - Mensagens de validação em inglês

### Configuração

-   `config/locale.php` - Configuração dos idiomas disponíveis
-   `app/Http/Middleware/SetLocale.php` - Middleware para detectar idioma
-   `app/Traits/HasTranslations.php` - Trait para facilitar uso das traduções

## Como Usar

### 1. Nos Controllers

```php
use App\Traits\HasTranslations;

class MeuController extends Controller
{
    use HasTranslations;

    public function index()
    {
        // Resposta de sucesso
        return $this->successResponse('general.success', $data);

        // Resposta de erro
        return $this->errorResponse('general.error', $errors, 400);

        // Erro de validação
        return $this->validationErrorResponse($validator->errors());
    }
}
```

### 2. Traduzir Mensagens

```php
// Traduzir uma chave
$message = $this->translate('auth.login_success');

// Traduzir com parâmetros
$message = $this->translate('validation.required', ['attribute' => 'email']);
```

### 3. Detecção Automática de Idioma

O middleware `SetLocale` detecta automaticamente o idioma através do header `Accept-Language`:

```bash
# Requisição em português
curl -H "Accept-Language: pt-BR" http://localhost:8000/api/test

# Requisição em inglês
curl -H "Accept-Language: en-US" http://localhost:8000/api/test
```

## Endpoints de Idioma

### GET /api/locales

Lista os idiomas disponíveis:

```json
{
    "success": true,
    "data": {
        "locales": {
            "pt-BR": "Português (Brasil)",
            "en-US": "English (United States)"
        },
        "default": "pt-BR",
        "fallback": "pt-BR"
    }
}
```

### GET /api/locale/current

Retorna o idioma atual:

```json
{
    "success": true,
    "data": {
        "locale": "pt-BR"
    }
}
```

## Estrutura das Mensagens

### Mensagens de Autenticação

-   `auth.register_success`
-   `auth.login_success`
-   `auth.logout_success`
-   `auth.invalid_credentials`
-   `auth.forgot_password_success`
-   `auth.reset_password_success`

### Mensagens de Validação

-   `validation.error`
-   `validation.required`
-   `validation.email`
-   `validation.min`
-   `validation.confirmed`

### Mensagens Gerais

-   `general.success`
-   `general.error`
-   `general.server_error`
-   `general.unauthorized`
-   `general.not_found`

### Mensagens de Recursos

-   `travel_request.created_success`
-   `travel_request.updated_success`
-   `travel_request.deleted_success`
-   `user.created_success`
-   `user.updated_success`
-   `user.deleted_success`

## Adicionando Novos Idiomas

1. Criar pasta em `resources/lang/{locale}/`
2. Copiar arquivos `messages.php` e `validation.php`
3. Traduzir as mensagens
4. Adicionar o idioma em `config/locale.php`

## Exemplo de Uso no Frontend

```javascript
// Definir idioma na requisição
const response = await fetch("/api/travel-requests", {
    headers: {
        "Accept-Language": "en-US",
        Authorization: `Bearer ${token}`,
    },
});
```

## Vantagens

1. **Consistência**: Todas as mensagens centralizadas
2. **Manutenibilidade**: Fácil de atualizar traduções
3. **Escalabilidade**: Fácil adicionar novos idiomas
4. **Automação**: Detecção automática de idioma
5. **Flexibilidade**: Suporte a parâmetros nas mensagens

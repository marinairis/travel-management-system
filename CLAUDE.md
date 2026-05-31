# Travel Management System

Sistema de gestão de pedidos de viagem corporativa — backend Laravel 12 + frontend Vue 3.

**Regras de implementação ficam nos CLAUDE.md de cada lado:**
- Backend: [`backend/CLAUDE.md`](backend/CLAUDE.md)
- Frontend: [`frontend/CLAUDE.md`](frontend/CLAUDE.md)
- Referência completa: [`docs/dev-skills.md`](docs/dev-skills.md)
- Glossário de domínio: [`docs/language-dictionary.md`](docs/language-dictionary.md)

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12, PHP 8.3 |
| Autenticação | JWT (tymon/jwt-auth) |
| Banco de dados | MySQL 8.0 |
| Frontend | Vue 3, Vite 7 |
| Estado | Pinia 3 |
| UI | Element Plus 2 |
| i18n | vue-i18n 9 (pt-BR padrão) |
| HTTP client | Axios 1.12 |

---

## Estrutura de pastas

```
travel-management-system/
├── backend/
│   ├── CLAUDE.md                   Regras Laravel — leia antes de tocar no backend
│   └── app/
│       ├── Http/Controllers/API/   Controllers invokable por domínio
│       ├── Http/Requests/          FormRequests com FailedValidationJson
│       ├── Services/               Regras de negócio
│       ├── Repositories/           Acesso ao banco
│       ├── Interfaces/             Contratos (Repositories e Services)
│       ├── Exceptions/             Exceptions por domínio
│       ├── Enums/                  Valores fixos tipados
│       ├── Models/                 Eloquent models
│       └── Traits/                 Comportamentos reutilizáveis
├── frontend/
│   ├── CLAUDE.md                   Regras Vue 3 — leia antes de tocar no frontend
│   └── src/
│       ├── views/                  Páginas (*View.vue)
│       ├── components/             Componentes reutilizáveis
│       ├── stores/                 Pinia stores por domínio
│       ├── composables/            Lógica reutilizável (use*)
│       ├── plugins/axios.js        HTTP client com interceptors
│       └── i18n/locales/           Traduções (pt-BR, en, es)
└── docs/
    ├── dev-skills.md               Referência completa de convenções
    └── language-dictionary.md      Glossário PT-BR ↔ EN
```

---

## Banco de dados

- **Engine**: MySQL 8.0
- **Container Docker**: `travel_db`
- **Database**: `travel_management`
- **Porta**: `3306`
- **ORM**: Eloquent — nunca interpolação direta de SQL

---

## Logs

### Logs de aplicação (erros de servidor)
- **Arquivo**: `backend/storage/logs/laravel.log`
- **Uso**: `Log::info()` / `Log::error()` com array de contexto

### Logs de atividade de negócio
- **Storage**: tabela MySQL `activity_logs`
- **Trait**: `backend/app/Traits/HasActivityLogging.php`

| Método | Quando usar |
|---|---|
| `$this->logActivityCreate()` | Após criar um registro |
| `$this->logActivityUpdate($oldData)` | Após atualizar |
| `$this->logActivityDelete()` | Antes de deletar |
| `$this->logActivityStatusChange($old, $new)` | Mudança de status |
| `$this->logActivity($action, $old, $new)` | Ação customizada |

---

## Comandos

### Docker
```bash
docker compose up -d
docker compose down
make up        # atalho
make reset     # zera e sobe do zero
```

### Backend
```bash
cd backend
composer install
php artisan migrate
php artisan serve             # http://localhost:8000
php artisan test              # PHPUnit
./vendor/bin/pint             # Formatação (Laravel Pint)
make test                     # atalho
```

### Frontend
```bash
cd frontend
npm install
npm run dev                   # http://localhost:5173
npm run build
npm run test                  # Vitest
npm run test:coverage
make test-frontend            # atalho
```

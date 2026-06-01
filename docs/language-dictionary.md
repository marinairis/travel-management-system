# Language Dictionary — Glossário de Domínio

Termos usados no código, banco de dados, traduções e comunicação do projeto. Use estes nomes consistentemente em variáveis, métodos, rotas, chaves de i18n e comentários de PR.

---

## Entidades principais

| PT-BR | EN (código) | Descrição |
|---|---|---|
| Pedido de Viagem | `TravelRequest` | Solicitação de viagem criada por um usuário |
| Destino | `Destination` | Local de destino de um pedido de viagem |
| Usuário | `User` | Qualquer pessoa autenticada no sistema |
| Convite | `Invitation` | Convite enviado por e-mail para cadastro no sistema |
| Log de Atividade | `ActivityLog` | Registro de ação realizada por um usuário no sistema |
| Notificação | `Notification` | Notificação enviada ao usuário sobre eventos do sistema |

---

## Papéis de usuário (Roles)

| PT-BR | EN (código) | Descrição |
|---|---|---|
| Solicitante | `requester` | Usuário que cria pedidos de viagem |
| Aprovador | `approver` | Usuário que aprova ou rejeita pedidos |
| Gestor | `manager` | Papel com permissões intermediárias (manager ou admin) |
| Administrador | `admin` | Acesso total ao sistema |

---

## Status do pedido de viagem

| PT-BR | EN (código) | Descrição |
|---|---|---|
| Pendente | `pending` | Pedido criado, aguardando aprovação |
| Aprovado | `approved` | Pedido aprovado pelo aprovador |
| Cancelado | `cancelled` | Pedido cancelado pelo solicitante ou gestor |
| Vencido | `expired` | Pedido não respondido dentro do prazo |

---

## Ações de log (`action` na tabela `activity_logs`)

| Código | Descrição |
|---|---|
| `create` | Criação de um registro |
| `update` | Atualização de um registro |
| `delete` | Remoção de um registro |
| `status_change` | Mudança de status de um pedido |

---

## Termos de integração

| PT-BR | EN (código) | Descrição |
|---|---|---|
| Cidades | `cities` | Dados geográficos de cidades (via IBGE) |
| IBGE | `IbgeService` | Serviço externo de dados geográficos brasileiros |
| Token | `token` | JWT de autenticação do usuário |
| Bearer | `Bearer` | Prefixo do header Authorization com JWT |

---

## Mapeamento i18n → domínio

Estrutura de chaves usada nos arquivos `src/i18n/locales/*.json`:

| Namespace i18n | Entidade/Contexto |
|---|---|
| `travelRequest.*` | Pedidos de viagem |
| `auth.*` | Autenticação (login, registro, senha) |
| `user.*` | Gestão de usuários |
| `notification.*` | Notificações |
| `activityLog.*` | Logs de atividade |
| `invitation.*` | Convites |
| `common.*` | Termos genéricos (salvar, cancelar, confirmar, etc.) |
| `error.*` | Mensagens de erro padronizadas |
| `status.*` | Status de pedidos |
| `role.*` | Papéis de usuário |

---

## Swagger Doc Files

| Termo | Significado |
|---|---|
| `Doc file` | Classe PHP abstrata em `app/Docs/` que contém apenas anotações `@OA\*` para o Swagger |
| `app/Docs/` | Pasta exclusiva para documentação OpenAPI — sem lógica de negócio |

---

## Rotas de API (padrão de nomenclatura)

| Recurso | Prefixo de rota |
|---|---|
| Pedidos de viagem | `/api/travel-requests` |
| Usuários | `/api/users` |
| Autenticação | `/api/auth` |
| Logs de atividade | `/api/activity-logs` |
| Notificações | `/api/notifications` |
| Convites | `/api/invitations` |
| Localizações (cidades) | `/api/locations` |

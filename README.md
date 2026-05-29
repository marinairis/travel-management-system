# Sistema de Gerenciamento de Viagens

Sistema completo de gerenciamento de viagens desenvolvido com Laravel (back-end) e Vue.js (front-end).

## 📋 Requisitos do Sistema

### Back-end (Laravel)

- PHP >= 8.3
- Composer
- SQLite (padrão) ou MySQL/PostgreSQL
- Node.js >= 20.19.0 ou >= 22.12.0
- NPM ou Yarn

### Front-end (Vue.js)

- Node.js >= 20.19.0 ou >= 22.12.0
- NPM ou Yarn

## 🚀 Instalação e Configuração

### 1. Clonar o Repositório

```bash
git clone <url-do-repositorio>
cd travel-management-system
```

### 2. Configuração do Back-end (Laravel)

#### Passo 1: Instalar Dependências

```bash
cd backend
composer install
```

#### Passo 2: Configurar Variáveis de Ambiente

Crie um arquivo `.env` na pasta `backend`:

```bash
cp .env.example .env
```

Ou crie manualmente com as seguintes variáveis essenciais:

```env
APP_NAME="Travel Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database (SQLite - padrão)
DB_CONNECTION=sqlite
DB_DATABASE=/caminho/completo/para/backend/database/database.sqlite

# OU para MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=travel_management
# DB_USERNAME=root
# DB_PASSWORD=

# JWT Authentication
JWT_SECRET=
JWT_TTL=60

# Cache, Queue e Session
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

# Email (opcional para desenvolvimento)
MAIL_MAILER=log
```

#### Passo 3: Gerar Chaves e Preparar Banco de Dados

```bash
# Gerar chave da aplicação
php artisan key:generate

# Gerar chave JWT
php artisan jwt:secret

# Criar arquivo do banco de dados SQLite (se usar SQLite)
touch database/database.sqlite

# Executar migrations
php artisan migrate

# (Opcional) Executar seeders para dados de teste
php artisan db:seed
```

#### Passo 4: Configurar Permissões

```bash
chmod -R 775 storage bootstrap/cache
```

### 3. Configuração do Front-end (Vue.js)

#### Passo 1: Instalar Dependências

```bash
cd ../frontend
npm install
```

#### Passo 2: Configurar Variáveis de Ambiente

Crie um arquivo `.env` na pasta `frontend`:

```bash
touch .env
```

Adicione as seguintes variáveis:

```env
VITE_API_URL=http://localhost:8000
```

## 🏃 Executando o Projeto

### Opção 1: Executar Back-end e Front-end Separadamente

#### Back-end

```bash
cd backend
php artisan serve
```

O back-end estará disponível em: `http://localhost:8000`

Se precisar de filas (queue):

```bash
php artisan queue:work
```

#### Front-end

```bash
cd frontend
npm run dev
```

O front-end estará disponível em: `http://localhost:5173`

### Opção 2: Executar Tudo com Composer (Back-end)

O Laravel possui um script conveniente para desenvolvimento:

```bash
cd backend
composer run dev
```

Este comando executa simultaneamente:

- Servidor Laravel (porta 8000)
- Queue listener
- Logs (Pail)
- Vite dev server

**Nota:** O front-end ainda precisa ser executado separadamente:

```bash
cd frontend
npm run dev
```

## 🧪 Executando os Testes

### Testes do Back-end (PHPUnit)

#### Executar todos os testes

```bash
cd backend
php artisan test
```

ou

```bash
composer test
```

#### Executar testes específicos

```bash
# Apenas testes unitários
php artisan test --testsuite=Unit

# Apenas testes de feature
php artisan test --testsuite=Feature

# Teste específico
php artisan test --filter=ExampleTest

# Com cobertura de código
php artisan test --coverage
```

#### Estrutura de Testes

- `tests/Unit/` - Testes unitários (funções isoladas, models, etc.)
- `tests/Feature/` - Testes de integração (APIs, controllers, etc.)

### Testes do Front-end (Vitest)

```bash
cd frontend
npm install           # instalar dependências incluindo vitest
npm run test          # executar testes uma vez
npm run test:watch    # modo watch (re-executa ao salvar)
npm run test:coverage # relatório de cobertura
```

Ou via Docker (sem precisar instalar Node localmente):

```bash
make test-frontend
```

#### Estrutura de Testes Frontend

- `src/__tests__/stores/auth.spec.js` — login, logout, persistência de token
- `src/__tests__/stores/travelRequest.spec.js` — CRUD e filtros de pedidos
- `src/__tests__/components/TravelRequestForm.spec.js` — validação do formulário e eventos
- `src/__tests__/components/TravelRequestTable.spec.js` — renderização e ações por papel

## 📦 Build para Produção

### Back-end

```bash
cd backend

# Otimizar autoloader
composer install --optimize-autoloader --no-dev

# Cachear configurações
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build dos assets
npm run build
```

### Front-end

```bash
cd frontend
npm run build
```

Os arquivos otimizados estarão na pasta `frontend/dist/`

## 🔧 Comandos Úteis

### Back-end (Laravel)

```bash
# Limpar caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Criar migration
php artisan make:migration create_table_name

# Criar model com migration e controller
php artisan make:model ModelName -mc

# Criar controller
php artisan make:controller ControllerName

# Listar rotas
php artisan route:list

# Abrir tinker (console interativo)
php artisan tinker
```

### Front-end (Vue.js)

```bash
# Verificar e corrigir código (linting)
npm run lint

# Formatar código
npm run format

# Preview do build de produção
npm run preview
```

## 🐳 Docker (Recomendado)

O método mais simples para executar o projeto completo (backend + frontend + MySQL) com um único comando.

### Pré-requisitos

Você precisa de dois softwares instalados: **Docker** e **make**.

---

#### 🐧 Linux (Ubuntu/Debian)

```bash
# Docker
sudo apt update
sudo apt install docker.io docker-compose-plugin

# make
sudo apt install make

# Adicionar seu usuário ao grupo docker (evita usar sudo)
sudo usermod -aG docker $USER
newgrp docker
```

---

#### 🪟 Windows (WSL2) — Recomendado para Windows

> O projeto roda dentro do WSL2 (subsistema Linux). É a forma mais confiável no Windows.

1. Instale o [Docker Desktop para Windows](https://www.docker.com/products/docker-desktop/) e ative a integração com WSL2 em **Settings → Resources → WSL Integration**
2. Abra o terminal WSL2 e instale o `make`:

```bash
sudo apt install make
```

3. Clone e rode o projeto dentro do terminal WSL2 (não no PowerShell/CMD)

---

#### 🪟 Windows (sem WSL2)

1. Instale o [Docker Desktop para Windows](https://www.docker.com/products/docker-desktop/)
2. Instale o `make` via [Chocolatey](https://chocolatey.org/) (abra o PowerShell como administrador):

```powershell
choco install make
```

Ou via [Scoop](https://scoop.sh/):

```powershell
scoop install make
```

3. Use o **PowerShell** ou **Git Bash** para rodar os comandos `make`

---

#### 🍎 macOS

```bash
# Docker Desktop
# Baixe em: https://www.docker.com/products/docker-desktop/

# make (já vem com o Xcode Command Line Tools)
xcode-select --install

# Ou via Homebrew
brew install make
```

---

### Passos para rodar o projeto

Após instalar os pré-requisitos, os passos são iguais em qualquer sistema:

```bash
# 1. Clonar o repositório
git clone <url-do-repositorio>
cd travel-management-system

# 2. Subir todos os serviços (primeira vez leva ~2 minutos)
make up

# 3. Verificar se os 3 containers estão rodando
make ps

# 4. Acompanhar a inicialização (opcional)
make logs-backend
```

Serviços disponíveis após iniciar:

| Serviço  | URL                                      |
|----------|------------------------------------------|
| Frontend | http://localhost:5173                    |
| Backend  | http://localhost:8000                    |
| Swagger  | http://localhost:8000/api/documentation  |
| MySQL    | localhost:3306                           |

### Comandos disponíveis (Makefile)

```bash
make up              # Sobe todos os containers em background
make down            # Para todos os containers
make build           # Reconstrói as imagens sem cache
make logs            # Acompanha todos os logs em tempo real
make logs-backend    # Logs apenas do backend
make logs-frontend   # Logs apenas do frontend
make reset           # Para, remove volumes e sobe do zero (banco limpo)
make test            # Roda testes do backend
make test-frontend   # Roda testes do frontend
make ps              # Lista status dos containers
make shell-backend   # Abre shell no container backend
make shell-frontend  # Abre shell no container frontend
```

### Primeira execução

Na primeira vez, o Docker vai:
1. Construir as imagens (PHP 8.3 + Node 20)
2. Instalar dependências (composer install + npm install)
3. Aguardar MySQL estar pronto
4. Gerar APP_KEY e JWT_SECRET automaticamente
5. Executar as migrations

### Solução de problemas Docker

```bash
# Ver o que está acontecendo nos containers
make logs

# Reiniciar do zero (apaga banco de dados)
make reset

# Reconstruir imagens do zero
make build && make up
```

## 🗂️ Estrutura do Projeto

```
travel-management-system/
├── backend/              # Aplicação Laravel
│   ├── app/             # Código da aplicação
│   ├── config/          # Arquivos de configuração
│   ├── database/        # Migrations, seeders e factories
│   ├── routes/          # Definição de rotas
│   ├── tests/           # Testes automatizados
│   └── .env             # Variáveis de ambiente
├── frontend/            # Aplicação Vue.js
│   ├── src/            # Código fonte
│   │   ├── components/ # Componentes Vue
│   │   ├── views/      # Views/Páginas
│   │   ├── router/     # Configuração de rotas
│   │   └── stores/     # Stores Pinia
│   └── .env            # Variáveis de ambiente
└── README.md           # Este arquivo
```

## 🔐 Autenticação

O sistema utiliza JWT (JSON Web Tokens) para autenticação:

- Biblioteca: `tymon/jwt-auth`
- Token TTL padrão: 60 minutos
- Refresh token disponível

## 🛠️ Tecnologias Utilizadas

### Back-end

- Laravel 12
- PHP 8.3
- JWT Auth
- Laravel Sanctum
- PHPUnit (testes)

### Front-end

- Vue.js 3
- Pinia (gerenciamento de estado)
- Vue Router (roteamento)
- Element Plus (componentes UI)
- Axios (requisições HTTP)
- Vite (build tool)

## 📖 Documentação da API (Swagger)

A documentação interativa da API é gerada automaticamente via OpenAPI (Swagger).

**Acesso:** http://localhost:8000/api/documentation

Para regenerar a documentação após alterar anotações nos controllers:

```bash
# Via Docker
docker compose exec backend php artisan l5-swagger:generate

# Localmente
cd backend
php artisan l5-swagger:generate
```

Endpoints documentados:
- **Auth**: register, login, logout, me, refresh, forgot-password, reset-password
- **Travel Requests**: CRUD completo, atualizar status, cancelar
- **Users**: listagem (admin), consulta, atualização, exclusão
- **Locations**: busca de municípios IBGE, listar destinos

## 📝 Variáveis de Ambiente Importantes

### Back-end (.env)

| Variável        | Descrição                        | Exemplo                  |
| --------------- | -------------------------------- | ------------------------ |
| `APP_NAME`      | Nome da aplicação                | Travel Management System |
| `APP_URL`       | URL da aplicação                 | http://localhost:8000    |
| `DB_CONNECTION` | Tipo de banco de dados           | sqlite, mysql, pgsql     |
| `DB_DATABASE`   | Nome/caminho do banco            | database.sqlite          |
| `JWT_SECRET`    | Chave secreta JWT                | (gerado automaticamente) |
| `JWT_TTL`       | Tempo de vida do token (minutos) | 60                       |
| `MAIL_MAILER`   | Driver de email                  | smtp, log, mailgun       |

### Front-end (.env)

| Variável         | Descrição           | Exemplo                   |
| ---------------- | ------------------- | ------------------------- |
| `VITE_API_URL`   | URL da API back-end | http://localhost:8000/api |
| `VITE_APP_TITLE` | Título da aplicação | Travel Management System  |

## 🐛 Troubleshooting

### Erro: "Permission denied" ao executar artisan

```bash
chmod +x artisan
```

### Erro: "Please provide a valid cache path"

```bash
php artisan cache:clear
chmod -R 775 storage bootstrap/cache
```

### Erro de CORS no front-end

Verifique o arquivo `config/cors.php` no back-end e certifique-se de que a origem do front-end está permitida.

### Porta já em uso

```bash
# Backend em outra porta
php artisan serve --port=8001

# Frontend em outra porta
npm run dev -- --port 5174
```

## 📄 Licença

MIT

## 👥 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📧 Suporte

Para suporte, abra uma issue no repositório ou entre em contato com a equipe de desenvolvimento.

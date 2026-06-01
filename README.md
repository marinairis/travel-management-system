# Travel Management System

Sistema de gestão de pedidos de viagem corporativa — backend Laravel 12 + frontend Vue 3.

---

## URLs de Acesso

| Serviço   | URL                                      |
|-----------|------------------------------------------|
| Frontend  | http://localhost:5173                    |
| Backend   | http://localhost:8000                    |
| Swagger   | http://localhost:8000/api/documentation  |
| Mailpit   | http://localhost:8025                    |

---

## Pré-requisitos

- **Docker** e **Docker Compose** instalados

---

## Como Rodar o Projeto

### 1. Clone e entre no projeto

```bash
git clone <url-do-repositorio>
cd travel-management-system
```

### 2. Suba os containers

```bash
docker compose up -d --build
```

Na primeira execução, o Docker irá automaticamente:
- Construir as imagens (PHP 8.3 + Node 20)
- Instalar dependências (`composer install` + `npm install`)
- Aguardar o MySQL estar pronto
- Gerar `APP_KEY` e `JWT_SECRET`
- Executar as migrations e o seeder

### 3. Verifique se está rodando

```bash
docker compose ps
```

### 4. Acesse o sistema

- Frontend: http://localhost:5173
- Backend: http://localhost:8000

---

## Credenciais de Acesso

O seeder cria automaticamente o seguinte usuário:

| Papel | E-mail                      | Senha     |
|-------|-----------------------------|-----------|
| Admin | admin@travelmanagement.com  | Admin@123 |

---

## Testes

### Backend (PHPUnit)

```bash
docker compose exec backend php artisan test
```

### Frontend (Vitest)

```bash
docker compose exec frontend npm run test
```

---

## Comandos Úteis

### Parar os containers

```bash
docker compose down
```

### Visualizar logs

```bash
# Todos os logs
docker compose logs -f

# Apenas backend
docker compose logs -f backend

# Apenas frontend
docker compose logs -f frontend
```

### Acessar shell dos containers

```bash
# Backend
docker compose exec backend sh

# Frontend
docker compose exec frontend sh
```

### Reset completo (remove banco e重建)

```bash
docker compose down -v
docker compose up -d --build
```

---

## make (atalhos opcionais)

Para comodidade, existem atalhos no arquivo `Makefile`. Example: `make up` é equivalente a `docker compose up -d`. Para ver todos os comandos disponíveis, consulte o arquivo `Makefile`.

---

## Rotinas Diárias Automatizadas

O Laravel Scheduler executa automaticamente dentro do container — nenhuma configuração de cron no host é necessária.

| Rotina                   | Frequência         | O que faz                                                                                    |
|--------------------------|--------------------|----------------------------------------------------------------------------------------------|
| `travel-requests:expire` | Diariamente, 00:00 | Marca como expirados os pedidos com status "solicitado" cuja data de partida já passou |

Para rodar manualmente:

```bash
docker compose exec backend php artisan travel-requests:expire
```

---

## Testando o Envio de E-mail

Todos os e-mails são capturados pelo **Mailpit** — nenhum e-mail real é enviado em desenvolvimento.

1. Acesse o Swagger em http://localhost:8000/api/documentation
2. Autentique-se com o usuário admin e use o endpoint `POST /api/invitations`
3. Acesse http://localhost:8025 para ver o convite na caixa de entrada

---

## Origem dos Dados de Localidades e Destinos

O endpoint `GET /api/locations/destinations` consome a **API pública do IBGE**:

```
https://servicodados.ibge.gov.br/api/v1/localidades/municipios
```

Os dados são cacheados por 24 horas no banco de dados para evitar chamadas repetidas.

---

## Documentação da API (Swagger)

Acesse http://localhost:8000/api/documentation para a documentação interativa.

Para regenerar após editar os arquivos em `backend/app/Docs/`:

```bash
docker compose exec backend php artisan l5-swagger:generate
```

---

## Variáveis de Ambiente

No Docker, as variáveis são injetadas automaticamente via `docker-compose.yml`. Para rodar localmente sem Docker, copie os exemplos:

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

### Principais configurações

| Variável          | Descrição                    | Padrão                    |
|-------------------|------------------------------|---------------------------|
| `VITE_API_URL`    | URL do backend (frontend)    | `http://localhost:8000`   |
| `DB_HOST`         | Host do banco                | `db`                      |
| `DB_DATABASE`     | Nome do banco                | `travel_management`       |
| `APP_KEY`         | Chave da aplicação           | Gerado automaticamente    |
| `JWT_SECRET`      | Chave do JWT                 | Gerado automaticamente    |

---

## Stack

| Camada          | Tecnologia             |
|-----------------|------------------------|
| Backend         | Laravel 12, PHP 8.3    |
| Autenticação    | JWT (tymon/jwt-auth)   |
| Banco de dados  | MySQL 8.0              |
| Frontend        | Vue 3, Vite 7          |
| Estado          | Pinia 3                |
| UI              | Element Plus 2         |
| HTTP client     | Axios 1.12             |
| Testes backend  | PHPUnit                |
| Testes frontend | Vitest                 |
| E-mail (dev)    | Mailpit                |
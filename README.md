# Travel Management System

Sistema de gestão de pedidos de viagem corporativa — backend Laravel 12 + frontend Vue 3.

---

## Acesso rápido

| Serviço   | URL                                      |
|-----------|------------------------------------------|
| Frontend  | http://localhost:5173                    |
| Backend   | http://localhost:8000                    |
| Swagger   | http://localhost:8000/api/documentation  |
| Mailpit   | http://localhost:8025                    |

---

## Pré-requisitos

Você precisa ter **Docker** e **make** instalados.

**Linux (Ubuntu/Debian)**
```bash
sudo apt update && sudo apt install docker.io docker-compose-plugin make
sudo usermod -aG docker $USER && newgrp docker
```

**Windows (WSL2) — recomendado**
1. Instale o [Docker Desktop](https://www.docker.com/products/docker-desktop/) e ative a integração WSL2 em _Settings → Resources → WSL Integration_
2. No terminal WSL2: `sudo apt install make`
3. Clone e rode o projeto dentro do terminal WSL2

**macOS**
```bash
# Docker Desktop: https://www.docker.com/products/docker-desktop/
xcode-select --install   # instala make junto com o Xcode CLI Tools
```

---

## Subindo o projeto

```bash
git clone <url-do-repositorio>
cd travel-management-system
make up
```

Na primeira execução o Docker irá:
1. Construir as imagens (PHP 8.3 + Node 20)
2. Instalar dependências (`composer install` + `npm install`)
3. Aguardar o MySQL estar pronto
4. Gerar `APP_KEY` e `JWT_SECRET` automaticamente
5. Executar as migrations e o seeder (cria o usuário admin)

Verifique se os containers estão rodando:
```bash
make ps
```

---

## Comandos make

| Comando              | O que faz                                         |
|----------------------|---------------------------------------------------|
| `make up`            | Sobe todos os containers em background            |
| `make down`          | Para todos os containers                          |
| `make build`         | Reconstrói as imagens sem cache                   |
| `make reset`         | Para, remove volumes e sobe do zero (banco limpo) |
| `make logs`          | Acompanha todos os logs em tempo real             |
| `make logs-backend`  | Logs apenas do backend                            |
| `make logs-frontend` | Logs apenas do frontend                           |
| `make test`          | Roda testes do backend (PHPUnit)                  |
| `make test-frontend` | Roda testes do frontend (Vitest)                  |
| `make ps`            | Lista status dos containers                       |
| `make shell-backend` | Abre shell no container backend                   |
| `make shell-frontend`| Abre shell no container frontend                  |

---

## Credenciais de acesso

Após `make up`, o seeder cria o seguinte usuário:

| Papel | E-mail                      | Senha     |
|-------|-----------------------------|-----------|
| Admin | admin@travelmanagement.com  | Admin@123 |

---

## Testes

**Backend (PHPUnit)**
```bash
make test
# ou diretamente:
docker compose exec backend php artisan test
```

**Frontend (Vitest)**
```bash
make test-frontend
# ou diretamente:
docker compose exec frontend npm run test
```

---

## Rotinas diárias automatizadas

O Laravel Scheduler executa automaticamente dentro do container — nenhuma configuração de cron no host é necessária.

| Rotina                   | Frequência         | O que faz                                                                                    |
|--------------------------|--------------------|----------------------------------------------------------------------------------------------|
| `travel-requests:expire` | Diariamente, 00:00 | Marca como expirados os pedidos com status "solicitado" cuja data de partida já passou |

Para rodar a rotina manualmente:
```bash
docker compose exec backend php artisan travel-requests:expire
```

---

## Testando o envio de e-mail (convite de usuários)

Todos os e-mails enviados pelo sistema são capturados pelo **Mailpit** — nenhum e-mail real é enviado em desenvolvimento.

1. Acesse o Swagger em http://localhost:8000/api/documentation
2. Autentique-se com o usuário admin e use o endpoint `POST /api/invitations`
3. Acesse http://localhost:8025 para ver o convite na caixa de entrada do Mailpit

---

## Origem dos dados de localidades e destinos

O endpoint `GET /api/locations/destinations` consome a **API pública do IBGE**:

```
https://servicodados.ibge.gov.br/api/v1/localidades/municipios
```

Os dados são cacheados por 24 horas no banco de dados para evitar chamadas repetidas. Nenhuma configuração extra é necessária — funciona automaticamente.

---

## Documentação da API (Swagger)

Acesse http://localhost:8000/api/documentation para a documentação interativa.

Para regenerar após editar os arquivos em `backend/app/Docs/`:
```bash
docker compose exec backend php artisan l5-swagger:generate
```

---

## Variáveis de ambiente

**Backend** — copie o exemplo e ajuste conforme necessário:
```bash
cp backend/.env.example backend/.env
```

No Docker, as variáveis são injetadas automaticamente via `docker-compose.yml` — o arquivo `.env` só é necessário para rodar localmente sem Docker.

**Frontend** — copie o exemplo:
```bash
cp frontend/.env.example frontend/.env
```

| Variável       | Descrição         | Padrão                  |
|----------------|-------------------|-------------------------|
| `VITE_API_URL` | URL do backend    | `http://localhost:8000` |

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

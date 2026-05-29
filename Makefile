up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

logs:
	docker compose logs -f

logs-backend:
	docker compose logs -f backend

logs-frontend:
	docker compose logs -f frontend

reset:
	docker compose down -v
	docker compose up -d

test:
	docker compose exec backend php artisan test

test-frontend:
	docker compose exec frontend npm run test

shell-backend:
	docker compose exec backend bash

shell-frontend:
	docker compose exec frontend sh

ps:
	docker compose ps

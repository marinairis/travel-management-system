#!/bin/bash
set -e

echo "==> Instalando dependências PHP..."
composer install --no-interaction --no-scripts --optimize-autoloader

# Cria .env a partir do .env.example se não existir
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Força configurações de banco e mail do ambiente Docker no .env
{
    grep -v "^DB_\|^#.*DB_\|^MAIL_\|^#.*MAIL_" .env
    echo "DB_CONNECTION=${DB_CONNECTION:-mysql}"
    echo "DB_HOST=${DB_HOST:-db}"
    echo "DB_PORT=${DB_PORT:-3306}"
    echo "DB_DATABASE=${DB_DATABASE:-travel_management}"
    echo "DB_USERNAME=${DB_USERNAME:-laravel}"
    echo "DB_PASSWORD=${DB_PASSWORD:-secret}"
    echo "MAIL_MAILER=${MAIL_MAILER:-log}"
    echo "MAIL_HOST=${MAIL_HOST:-127.0.0.1}"
    echo "MAIL_PORT=${MAIL_PORT:-2525}"
    echo "MAIL_USERNAME=${MAIL_USERNAME:-}"
    echo "MAIL_PASSWORD=${MAIL_PASSWORD:-}"
    echo "MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-}"
    echo "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-noreply@travelsystem.com}"
} > /tmp/.env.tmp && mv /tmp/.env.tmp .env

echo "==> Gerando APP_KEY..."
php artisan key:generate --no-interaction --force

echo "==> Gerando JWT_SECRET..."
php artisan jwt:secret --no-interaction --force

echo "==> Aguardando banco de dados estar pronto..."
until php -r "
try {
    new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "Banco não disponível, aguardando..."
    sleep 2
done
echo "==> Banco de dados pronto!"

echo "==> Executando migrations..."
php artisan migrate --no-interaction --force

echo "==> Executando seeders..."
php artisan db:seed --no-interaction --force

echo "==> Gerando documentação Swagger..."
php artisan l5-swagger:generate || echo "==> Aviso: Swagger não gerado (anotações incompletas), continuando..."

echo "==> Iniciando queue worker em background..."
php artisan queue:work --daemon --tries=3 --sleep=3 &

echo "==> Iniciando servidor Laravel na porta 8000..."
exec php artisan serve --host=0.0.0.0 --port=8000

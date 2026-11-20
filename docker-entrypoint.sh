#!/bin/sh
set -e

echo "🚀 Iniciando JIHOVA en EasyPanel..."

# Crear estructura de carpetas de almacenamiento si no existen
mkdir -p /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/framework/cache \
         /app/storage/logs \
         /app/bootstrap/cache

chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Crear enlace simbólico de almacenamiento público
php artisan storage:link --force || true

# Ejecutar migraciones si RUN_MIGRATIONS es true
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "📦 Ejecutando migraciones y seeders de base de datos..."
    php artisan migrate --force --seed || true
fi

# Optimizar caché de configuración, rutas y vistas en producción
if [ -n "$APP_KEY" ]; then
    echo "⚡ Optimizando caché de configuración, rutas y vistas..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "✨ Servidor web listo para recibir tráfico en https://jihova.app"
exec "$@"

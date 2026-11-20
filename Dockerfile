# ==========================================
# Fase 1: Compilación de Assets con Node / Vite
# ==========================================
FROM node:22-alpine AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ==========================================
# Fase 2: Servidor de Producción con FrankenPHP
# ==========================================
FROM dunglas/frankenphp:1-php8.3

# Instalar extensiones PHP necesarias para Laravel, MySQL y GD
RUN install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    gd \
    intl \
    zip \
    opcache \
    bcmath \
    pcntl

# Variables de entorno para FrankenPHP / Caddy
ENV SERVER_NAME=":80"
ENV APP_ENV="production"
ENV APP_DEBUG="false"

WORKDIR /app

# Copiar Composer binario
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar código fuente
COPY . /app

# Copiar assets compilados de Vite
COPY --from=node_builder /app/public/build /app/public/build

# Copiar configuración de Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Instalar dependencias de producción de Composer
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Ajustar permisos de directorios de almacenamiento y caché
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Configurar el script de inicio (entrypoint)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

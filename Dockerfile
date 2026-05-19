FROM composer:2 AS builder
WORKDIR /app
RUN composer create-project laravel/laravel . --prefer-dist --no-interaction --quiet

FROM php:8.4-cli-alpine
RUN apk add --no-cache oniguruma-dev libxml2-dev curl-dev mysql-dev \
    && docker-php-ext-install mbstring xml curl pdo pdo_mysql
COPY --from=builder /app /app
COPY overrides/ /app/
WORKDIR /app
RUN php artisan key:generate \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database
USER www-data
EXPOSE 8000
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]

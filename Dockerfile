FROM php:8.3-cli-bookworm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql zip bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && groupadd -g "${GID}" app \
    && useradd --uid "${UID}" --gid "${GID}" --create-home --shell /bin/bash app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Switch to non-root user (match host UID/GID via build args if bind-mount permissions break)
USER app

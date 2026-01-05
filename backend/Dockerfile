FROM php:8.2-fpm

# --- CORREÇÃO 1: Definir os ARGs aqui para o Docker saber que eles existem ---
ARG user
ARG uid

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Instala extensões do PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Limpa cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cria o usuário do sistema
# Agora as variáveis $user e $uid vão funcionar porque foram declaradas lá em cima
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Instala o Composer (Pode ser feito antes de mudar o usuário)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www

# --- CORREÇÃO 2: Mudar o usuário por último ---
# A partir daqui, qualquer comando (como php artisan) rodará como 'alisson'
USER $user
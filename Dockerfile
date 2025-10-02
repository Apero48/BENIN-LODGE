# Dockerfile pour PHP + Apache avec extensions SQL Server
# Basé sur une image php officielle avec Apache et apt pour installer dépendances
FROM php:8.2-apache-bullseye

# Variables d'environnement pour non-interactif
ENV DEBIAN_FRONTEND=noninteractive

# Installer dépendances système nécessaires pour driver SQL Server
RUN apt-get update && apt-get install -y --no-install-recommends \
    gnupg2 ca-certificates apt-transport-https wget \
    unixodbc-dev g++ make autoconf libc-dev pkg-config git \
    libzip-dev zlib1g-dev && \
    rm -rf /var/lib/apt/lists/*

# Ajouter les sources Microsoft pour ODBC et SQLSRV
# Ajouter le dépôt officiel Microsoft pour Debian 11 (bullseye)
RUN apt-get update && apt-get install -y --no-install-recommends curl gnupg2 apt-transport-https lsb-release && rm -rf /var/lib/apt/lists/*

RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
RUN curl -sSL https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list

# Installer le driver msodbcsql18 requis par sqlsrv/pdo_sqlsrv
RUN apt-get update && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 unixodbc-dev && rm -rf /var/lib/apt/lists/*

# Installer pecl et extensions PHP nécessaires
RUN pecl channel-update pecl.php.net || true
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev zlib1g-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql zip

# Installer sqlsrv et pdo_sqlsrv via pecl
RUN pecl install sqlsrv pdo_sqlsrv && docker-php-ext-enable sqlsrv pdo_sqlsrv || true

# Copier le code de l'application
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Exposer le port HTTP
EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]

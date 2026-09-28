FROM php:8.2-apache
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev && docker-php-ext-install curl
COPY . /var/www/html/
RUN a2enmod rewrite
RUN echo "PassEnv API_BASE_URL" >> /etc/apache2/apache2.conf
EXPOSE 80


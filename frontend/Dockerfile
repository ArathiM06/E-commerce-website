FROM php:8.2-apache
COPY . /var/www/html/
RUN a2enmod rewrite
RUN echo "PassEnv API_BASE_URL" >> /etc/apache2/apache2.conf
EXPOSE 80

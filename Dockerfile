#Myadmin          http://localhost:8080

#Myadmin          http://localhost:8080
# Use PHP with Apache

FROM php:8.2-apache

# Install mysqli extension for MySQL support
RUN docker-php-ext-install mysqli

# Enable Apache's mod_rewrite (optional but good for clean URLs)
RUN a2enmod rewrite

# Copy your application into Apache's web directory
COPY . /var/www/html/

# Fix permissions to avoid 403 Forbidden
RUN chmod -R 755 /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (Render auto-detects this)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

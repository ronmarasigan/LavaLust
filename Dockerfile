# Use official PHP 8.2 with Apache image
FROM php:8.2-apache

# Enable Apache mod_rewrite (required for LavaLust URL routing)
RUN a2enmod rewrite

# Set the Apache document root to LavaLust's 'public' folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess files to override settings (needed for LavaLust)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy all your project files into the container
COPY . /var/www/html

# Set the working directory
WORKDIR /var/www/html

# Set proper permissions for LavaLust's runtime and app folders
RUN chown -R www-data:www-data /var/www/html/runtime /var/www/html/app /var/www/html/public

# Expose port 80
EXPOSE 80

# Replace default port 80 with Render's dynamic PORT variable and start Apache
CMD sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf && apache2-foreground
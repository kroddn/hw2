FROM php:7.2-apache
RUN apt-get update  \
  && apt-get install -y \
    libmariadb-dev-compat \
    libmariadb-dev \
    default-mysql-client \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    cron
RUN docker-php-ext-install mysqli \
  && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install -j$(nproc) gd


CMD ["/usr/sbin/apachectl", "-D", "FOREGROUND"]

## Cronjob
# Copy hello-cron file to the cron.d directory
COPY points-cron /etc/cron.d/points-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/points-cron

# Apply cron job
RUN crontab /etc/cron.d/points-cron

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

# Run the command on container startup
RUN cron

RUN ./var/www/cron/service.sh
RUN ./var/www/cron/hwha_dev
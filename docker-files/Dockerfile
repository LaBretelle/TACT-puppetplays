FROM ubuntu:20.04

MAINTAINER Arnaud Bey & Sylvain Hatier

RUN apt-get -qqq update && DEBIAN_FRONTEND=noninteractive apt-get install -qqq -y \
        apt-utils \
        git \
        make \
        vim \
        wget \
        curl \
        gdebi \
        apache2 \
        openssl \
        php7.4 \
        libapache2-mod-php7.4 \
        php7.4-bcmath \
        php7.4-cli \
        php7.4-common \
        php7.4-curl \
        php7.4-dev \
        php7.4-fpm \
        php7.4-intl \
        php7.4-json \
        php7.4-mbstring \
        php7.4-mysql \
        php7.4-opcache \
        php7.4-readline \
        php7.4-xml \
        php7.4-zip \
        php7.4-gd \
        php-common \
        php-imagick

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer

# Install npm
RUN curl -o /usr/local/bin/n https://raw.githubusercontent.com/visionmedia/n/master/bin/n
RUN chmod +x /usr/local/bin/n
RUN n stable

RUN mkdir /var/www/public

# Configure Apache
RUN rm -rf /etc/apache2/sites-available/* && rm -rf /etc/apache2/sites-enabled/*

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
ADD ./app.conf /etc/apache2/sites-available/app.conf
ADD ./app-ssl.conf /etc/apache2/sites-available/app-ssl.conf

RUN a2ensite app.conf
RUN a2ensite app-ssl.conf
RUN a2enmod headers
RUN a2enmod deflate
RUN a2enmod rewrite
RUN a2enmod ssl
RUN service apache2 restart

WORKDIR /var/www/

# as www-data user
RUN usermod -u 1000 www-data

EXPOSE 80
EXPOSE 443

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

FROM ubuntu:18.04

MAINTAINER Patrick Guillou

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
        python2.7 \
        python-pip \
        php7.2 \
        libapache2-mod-php7.2 \
        php7.2-bcmath \
        php7.2-cli \
        php7.2-common \
        php7.2-curl \
        php7.2-dev \
        php7.2-fpm \
        php7.2-intl \
        php7.2-json \
        php7.2-mbstring \
        php7.2-mysql \
        php7.2-opcache \
        php7.2-readline \
        php7.2-xml \
        php7.2-zip \
        php7.2-gd \
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

# For local dev
ADD ./certs/server.crt /etc/ssl/certs/server.crt
ADD ./certs/server.key /etc/ssl/private/server.key

# For production ( /!!!!\ you have to copy the .crt and .key files in docker-files/certs folder) :
# ADD ./certs/star_demarre-shs_fr.crt /etc/ssl/certs/server.crt
# ADD ./certs/demarre-shs.fr.key /etc/ssl/private/server.key

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

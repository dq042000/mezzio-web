#
#--------------------------------------------------------------------------
# Image Setup
#--------------------------------------------------------------------------
#
ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-fpm

# Set Environment Variables
ENV DEBIAN_FRONTEND=noninteractive

#
#--------------------------------------------------------------------------
# Software's Installation
#--------------------------------------------------------------------------
#
# Installing tools and PHP extentions using "apt", "docker-php", "pecl",
#

# Install "curl", "libmemcached-dev", "libpq-dev", "libjpeg-dev",
#         "libpng-dev", "libfreetype6-dev", "libssl-dev", "libmcrypt-dev",
RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
        curl \
        libmemcached-dev \
        libz-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libfreetype6-dev \
        libssl-dev \
        libwebp-dev \
        libxpm-dev \
        libmcrypt-dev \
        libonig-dev \
        python3 \
        python3-pip \
        libreoffice; \
    rm -rf /var/lib/apt/lists/*

# 安裝 python3 的套件
RUN pip3 install --break-system-packages pymupdf

# 安裝 Docker CLI
RUN curl -fsSL https://download.docker.com/linux/static/stable/x86_64/docker-20.10.8.tgz | tar xzvf - --strip-components=1 -C /usr/local/bin docker/docker

# 將 www-data 加入 docker 群組並讓其設定生效
RUN groupadd -f docker && usermod -aG docker www-data && newgrp docker

# 安裝 php 的套件
RUN set -eux; \
    # Install the PHP pdo_mysql extention
    docker-php-ext-install pdo_mysql; \
    # Install the PHP pdo_pgsql extention
    docker-php-ext-install pdo_pgsql; \
    # Install the PHP gd library
    docker-php-ext-configure gd \
        --prefix=/usr \
        --with-jpeg \
        --with-webp \
        --with-xpm \
        --with-freetype; \
    docker-php-ext-install gd; \
    php -r 'var_dump(gd_info());'

RUN set -xe; \
    apt-get update -yqq && \
    pecl channel-update pecl.php.net && \
    apt-get install -yqq \
    apt-utils \
    gnupg2 \
    git \
    libzip-dev zip unzip && \
    if [ ${LARADOCK_PHP_VERSION} = "7.3" ] || [ ${LARADOCK_PHP_VERSION} = "7.4" ] || [ $(php -r "echo PHP_MAJOR_VERSION;") = "8" ]; then \
        docker-php-ext-configure zip; \
    else \
        docker-php-ext-configure zip --with-libzip; \
    fi && \
    docker-php-ext-install zip && \
    php -m | grep -q 'zip'

###########################################################################
# LDAP:
###########################################################################
ARG INSTALL_LDAP=true
RUN if [ ${INSTALL_LDAP} = true ]; then \
    apt-get install -yqq libldap2-dev && \
    ARCH=$(arch)   && \
    docker-php-ext-configure ldap --with-libdir="lib/${ARCH}-linux-gnu/" && \
    docker-php-ext-install ldap \
;fi

###########################################################################
# MongoDB:
###########################################################################
ARG INSTALL_MONGO=true
ARG MONGO_VERSION=1.21.0
ARG SPECIFY_MONGO_VERSION=false
RUN if [ ${INSTALL_MONGO} = true ]; then \
    # Install the mongodb extension
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
        pecl install mongo; \
        docker-php-ext-enable mongo; \
        php -m | grep -oiE '^mongo$'; \
    else \
        if [ $(php -r "echo PHP_MAJOR_VERSION;") = "7" ] && [ $(php -r "echo PHP_MINOR_VERSION;") != "4" ]; then \
            if [ $(php -r "echo PHP_MINOR_VERSION;") = "0" ] || [ $(php -r "echo PHP_MINOR_VERSION;") = "1" ]; then \
                pecl install mongodb-1.9.2; \
            else \
                pecl install mongodb-1.16.2; \
            fi; \
        else \
            if [ ${SPECIFY_MONGO_VERSION} = true ]; then \
                pecl install mongodb-${MONGO_VERSION}; \
            else \
                pecl install mongodb; \
            fi; \
        fi; \
        docker-php-ext-enable mongodb; \
        php -m | grep -oiE '^mongodb$'; \
    fi; \
fi

###########################################################################
# Human Language and Character Encoding Support:
###########################################################################
ARG INSTALL_INTL=true
RUN if [ ${INSTALL_INTL} = true ]; then \
    # Install intl and requirements
    apt-get install -yqq zlib1g-dev libicu-dev g++ && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl \
;fi

###########################################################################
# Mysqli Modifications:
###########################################################################
ARG INSTALL_MYSQLI=true
RUN if [ ${INSTALL_MYSQLI} = true ]; then \
    docker-php-ext-install mysqli \
;fi

###########################################################################
# PHP REDIS EXTENSION
###########################################################################
ARG INSTALL_PHPREDIS=true
RUN if [ ${INSTALL_PHPREDIS} = true ]; then \
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
        pecl install -o -f redis-4.3.0; \
    elif [ $(php -r "echo PHP_MAJOR_VERSION;") = "7" ] && { [ $(php -r "echo PHP_MINOR_VERSION;") = "0" ] || [ $(php -r "echo PHP_MINOR_VERSION;") = "1" ]; }; then \
        pecl install -o -f redis-5.3.7; \
    else \
        pecl install -o -f redis; \
    fi \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis \
;fi

###########################################################################
# xDebug:
###########################################################################
ARG INSTALL_XDEBUG=true
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    # Install the xdebug extension
    # https://xdebug.org/docs/compat
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "8" ] || { [ $(php -r "echo PHP_MAJOR_VERSION;") = "7" ] && { [ $(php -r "echo PHP_MINOR_VERSION;") = "4" ] || [ $(php -r "echo PHP_MINOR_VERSION;") = "3" ]; } ;}; then \
        if [ $(php -r "echo PHP_MAJOR_VERSION;") = "8" ]; then \
            pecl install xdebug-3.3.0; \
        else \
            pecl install xdebug-3.1.6; \
        fi; \
    else \
        if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
            pecl install xdebug-2.5.5; \
        else \
            if [ $(php -r "echo PHP_MINOR_VERSION;") = "0" ]; then \
                pecl install xdebug-2.9.0; \
            else \
                pecl install xdebug-2.9.8; \
            fi \
        fi \
    fi && \
    docker-php-ext-enable xdebug \
;fi

###########################################################################
# ImageMagick:
###########################################################################
USER root
ARG INSTALL_IMAGEMAGICK=true
ARG IMAGEMAGICK_VERSION=latest
ENV IMAGEMAGICK_VERSION=${IMAGEMAGICK_VERSION}
RUN if [ ${INSTALL_IMAGEMAGICK} = true ]; then \
    apt-get install -yqq libmagickwand-dev imagemagick && \
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "8" ]; then \
        cd /tmp && \
        if [ ${IMAGEMAGICK_VERSION} = "latest" ]; then \
            git clone https://github.com/Imagick/imagick; \
        else \
            git clone --branch ${IMAGEMAGICK_VERSION} https://github.com/Imagick/imagick; \
        fi && \
        cd imagick && \
        phpize && \
        ./configure && \
        make && \
        make install && \
        rm -r /tmp/imagick; \
    else \
        pecl install imagick; \
    fi && \
    docker-php-ext-enable imagick; \
    php -m | grep -q 'imagick' \
;fi

###########################################################################
# PHP Memcached:
###########################################################################
ARG INSTALL_MEMCACHED=true
RUN if [ ${INSTALL_MEMCACHED} = true ]; then \
    # Install the php memcached extension
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
        echo '' | pecl -q install memcached-2.2.0; \
    else \
        echo '' | pecl -q install memcached; \
    fi \
    && docker-php-ext-enable memcached \
;fi

###########################################################################
# XSL:
###########################################################################
ARG INSTALL_XSL=true
RUN if [ ${INSTALL_XSL} = true ]; then \
    # Install the xsl extension
    apt-get -y install libxslt-dev && \
    docker-php-ext-install xsl \
;fi

###########################################################################
# APCU:
###########################################################################
ARG INSTALL_APCU=false
RUN if [ ${INSTALL_APCU} = true ]; then \
    if [ $(php -r "echo PHP_MAJOR_VERSION;") = "5" ]; then \
        pecl install -a apcu-4.0.11; \
    else \
        pecl install apcu; \
    fi && \
    docker-php-ext-enable apcu \
;fi

###########################################################################
# Composer:
###########################################################################
# RUN curl https://getcomposer.org/installer > composer-setup.php \
#     && php composer-setup.php \ 
#     && mv composer.phar /usr/local/bin/composer \ 
#     && rm composer-setup.php;
ARG PHP_COMPOSER_VERSION=2.2.22
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=${PHP_COMPOSER_VERSION}

###########################################################################
# SUPERVISOR:
###########################################################################
ARG INSTALL_SUPERVISOR=false
RUN if [ ${INSTALL_SUPERVISOR} = true ]; then \
    apt-get update && apt-get install -y supervisor && \
    touch /run/supervisor.sock && \
    chmod 777 /run/supervisor.sock && \
    chmod 777 /run && \
    echo_supervisord_conf > /etc/supervisord.conf && \
    sed -i 's/\;\[include\]/\[include\]/g' /etc/supervisord.conf && \
    sed -i 's/\;files\s.*/files = supervisord.d\/*.conf/g' /etc/supervisord.conf \
;fi

###########################################################################
# Clean up:
###########################################################################
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/app
EXPOSE 9000

COPY supervisor/supervisord.conf /etc/supervisord.conf

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
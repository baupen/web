#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# install git which is required by composer
apt-get update -yqq
apt-get install git -yqq

# install phpunit
curl --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# install php extensions
docker-php-ext-install sqlite3
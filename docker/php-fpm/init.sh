#!/bin/sh

set -e

if [ ! -f config/jwt/public.pem ] || [ ! -f config/jwt/private.pem ]
then
  mkdir -p config/jwt
  openssl genrsa -out config/jwt/private.pem -passout pass:$JWT_PASSPHRASE -aes256 4096
  chmod +r config/jwt/private.pem
  openssl rsa -passin pass:$JWT_PASSPHRASE -pubout -in config/jwt/private.pem -out config/jwt/public.pem
fi

./wait-for-it.sh loire_api_db:5432 -- echo "Database is up !"

bin/console do:da:drop --if-exists --force
bin/console do:da:cr --if-not-exists -q
bin/console do:mi:mi -n

php-fpm

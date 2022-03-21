#!/bin/bash

if [ $# -ne 1 ]
then
    echo "Usage: $0 [on|off]"; exit 1;
fi

case "$1" in
    on)
        sed -i 's/^;zend_extension/zend_extension/' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
        ;;
    off)
        sed -i 's/^zend_extension/;zend_extension/' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
        ;;
    *)
        echo "Usage: $0 [on|off]"; exit 1;
        ;;
esac

echo "xdebug is now $1"

FROM drupaldocker/php:7.3-cli-2.x

RUN composer self-update \
    && apk add --no-cache libzip-dev

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
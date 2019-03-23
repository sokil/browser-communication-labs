FROM alpine:3.9

ENV NGINX_VERSION 1.15.9
ENV NGINX_PUSH_STREAM_MODULE_VERSION 0.5.4

RUN apk update && \
    # install requirements
    apk add --no-cache --virtual .build-deps make curl gcc libc-dev musl-dev pcre-dev zlib-dev linux-headers && \
    apk add --no-cache --virtual .run-deps ca-certificates pcre bash && \
    # update certificates
    update-ca-certificates && \
    # create user and group
    addgroup -S nginx && \
    adduser -D -S -h /var/cache/nginx -s /sbin/nologin -G nginx nginx && \
    # build dir
    mkdir /usr/build && \
    cd /usr/build && \
    # download nginx
    curl -fSL https://nginx.org/download/nginx-$NGINX_VERSION.tar.gz -o nginx.tar.gz && \
    tar -zxvf nginx.tar.gz && \
    # download push stream module
    curl -fSL https://github.com/wandenberg/nginx-push-stream-module/archive/$NGINX_PUSH_STREAM_MODULE_VERSION.tar.gz -o nginx-push-stream-module.tar.gz && \
    tar -zxvf nginx-push-stream-module.tar.gz && \
    # configure
    cd nginx-$NGINX_VERSION && \
    ./configure \
        --sbin-path=/usr/sbin/nginx \
        --conf-path=/etc/nginx/nginx.conf \
        --user=nginx \
        --group=nginx \
        --error-log-path=/var/log/nginx/error.log \
        --http-log-path=/var/log/nginx/access.log \
        --add-module=../nginx-push-stream-module-$NGINX_PUSH_STREAM_MODULE_VERSION && \
    # install and finish
    make && \
    make install clean && \
    # clear deps
    apk del .build-deps

COPY nginx.conf /etc/nginx/nginx.conf
COPY conf.d/longpolling.conf /etc/nginx/conf.d/longpolling.conf

EXPOSE 80

STOPSIGNAL SIGTERM

CMD ["nginx", "-g", "daemon off;"]

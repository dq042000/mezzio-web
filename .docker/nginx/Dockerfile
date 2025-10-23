# 使用 nginx 官方的 alpine 版本作為基礎鏡像
FROM nginx:alpine

# 將本地的 nginx.conf 配置文件複製到鏡像的 /etc/nginx 目錄下
COPY nginx.conf /etc/nginx/

# 將本地的 logrotate/nginx 配置文件複製到鏡像的 /etc/logrotate.d 目錄下
COPY logrotate/nginx /etc/logrotate.d/

COPY logrotate/daily/logrotate /etc/periodic/daily/logrotate

# 設定 /etc/logrotate.d/nginx 文件的權限為 644
RUN chmod 644 /etc/logrotate.d/nginx

# 更新 alpine 的套件庫，升級現有套件，安裝 logrotate、openssl 和 bash 套件
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories
RUN apk update \
    && apk upgrade \
    && apk --update add logrotate \
    && apk add --no-cache openssl \
    && apk add --no-cache bash

# 安裝 curl 套件，不使用緩存
RUN apk add --no-cache curl

# 將啟動腳本複製到容器
COPY start.sh /start.sh

# 使啟動腳本可執行
RUN chmod +x /start.sh

# 設置啟動腳本為 CMD
CMD ["/start.sh"]

EXPOSE 80 8080 443
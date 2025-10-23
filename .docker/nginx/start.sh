#!/bin/bash

# 啟動 crond 排程服務，通常用於 logrotate 等排程任務
crond -l 2 -b

# 啟動 Nginx 伺服器（以 foreground 模式，讓容器保持運作）
exec nginx -g 'daemon off;'
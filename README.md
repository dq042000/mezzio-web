# mezzio-web

## 安裝手冊

請依照以下步驟完成 mezzio-web 專案的安裝與啟動：

### 1. 複製設定檔

```bash
cp docker-compose.yml.sample docker-compose.yml
cp env-sample .env
cp .docker/nginx/default.conf.dist .docker/nginx/conf.d/default.conf
```

### 2. 調整資料夾權限

確保 PHP 服務可正確存取資料目錄：

```bash
sudo chmod -R 777 ./web/php/data
```

### 3. 建立硬連結

將 .env 檔案連結到 PHP 設定目錄：

```bash
ln .env ./web/php/config/.env
```

### 4. 設定環境參數

請依實際需求編輯 `.env` 檔案，設定資料庫、Redis、MongoDB 等相關參數。

### 5. 啟動專案

使用 Docker Compose 啟動所有服務：

```bash
docker compose up -d
```

---

### 其他說明

- 預設服務包含 Nginx、PHP、MariaDB、MongoDB、Redis 等，相關連接埠與帳號密碼請參考 `.env` 檔案。
- 若需自訂 PHP、資料庫等版本，請於 `.env` 內調整對應參數。
- 啟動後可透過 `docker compose ps` 檢查服務狀態。
- 若需停止服務，可使用 `docker compose down` 指令。


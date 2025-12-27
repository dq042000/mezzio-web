# mezzio-web

## 安裝手冊

建議直接使用 `setup.sh` 自動化安裝選單，快速完成專案初始化、啟動與常用操作。

### 一鍵安裝與啟動

```bash
chmod +x setup.sh
./setup.sh
```

執行後會出現互動式選單，主要功能如下：

- (1) 專案初始化 + 啟動開發環境（自動複製設定檔、建立連結、啟動容器、安裝 PHP 套件、關閉快取）
- (2) 啟動開發環境（啟動容器、安裝/更新 PHP 套件、關閉快取）
- (3) 執行 CLI 工具（進入互動式 CLI 指令模式）
- (4) 執行 Composer（進入互動式 composer 指令模式）
- (5) 執行 Migrate（包含 workbench export、產生/執行/還原/更新 migration）
- (Q) 離開

> **小提醒**：
> - 第一次建議選 (1) 完整初始化。
> - 之後可用 (2) 快速啟動開發環境。

### 傳統手動安裝步驟（供參考）

1. 複製設定檔
	```bash
	cp docker-compose.yml.sample docker-compose.yml
	cp env-sample .env
	cp .docker/nginx/default.conf.dist .docker/nginx/conf.d/default.conf
	```
2. 調整資料夾權限
	```bash
	sudo chmod -R 777 ./web/php/data
	```
3. 建立硬連結
	```bash
	ln .env ./web/php/config/.env
	```
4. 設定 `.env` 參數（資料庫、Redis、MongoDB 等）
5. 啟動專案
	```bash
	docker compose up -d
	```

---

### 其他說明

- 預設服務包含 Nginx、PHP、MariaDB、MongoDB、Redis 等，相關連接埠與帳號密碼請參考 `.env` 檔案。
- 若需自訂 PHP、資料庫等版本，請於 `.env` 內調整對應參數。
- 啟動後可透過 `docker compose ps` 檢查服務狀態。
- 若需停止服務，可使用 `docker compose down` 指令。


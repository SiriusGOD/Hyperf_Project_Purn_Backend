# 安裝方法

## 環境要求

需先安裝 docker

## 安裝步驟

1. git clone 此目錄
2. git clone https://github.com/Laradock/laradock.git 到此專案底下
3. cp .env.laradock .env
4. 切到 laradock 底下，增加 port in docker-compose.yml 如下

```yaml
### Workspace Utilities ##################################
###....###
ports:
  - "9501:9501"
```
5. 複製 laradock 底下的 .env.example to .env

6. cd laradock && docker-compose up -d workspace redis mysql

7. docker-compose exec workspace
8. pecl install swoole ，記得在 enable openssl support 輸入 yes
9. 進入 mysql 新增一個資料庫名為 hyperf 資料庫(可以從外部本機連127.0.0.1:3306 或者進入 mysql docker 指令建立)
10. sh deploy.sh
11. sh start.sh
12. 訪問 localhost:9501 看是否有執行成功

## 常見問題

1. redis 報錯 no auth : .env 的 REDIS_AUTH 設置不對，建議與 laradock .env 的 REDIS_PASSWORD 比較是否一致
2. hyperf 無法啟動 ： 可能是因為以下幾個原因：

註解語法錯誤 : 例如使用 inject 卻未引入 inject class
單純語法錯誤 : 檢查 ide 是否哪裏有報錯
目錄權限不對 : hyperf 需建立 runtime 資料夾
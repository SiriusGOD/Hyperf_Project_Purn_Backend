# navigation

## 測試環境說明

### 目錄地點

/var/www/navigation

### 重新啟動方法
目前使用 pm2 handle 
sh reload.sh 

### mysql

mysql 在本地
默认 mysql 密码在 /root/mysql.pwd

### redis

redis 在本地
無密碼

### 連結網址

[http://172.104.46.27:9501/](http://172.104.46.27:9501/)


[圖片上傳 先顯示在頁面](https://www.raymondcamden.com/2013/10/01/MultiFile-Uploads-and-Multiple-Selects)

[安裝 imagick] (https://ghost.rivario.com/docker-php-7-2-fpm-alpine-imagick/)

要注意權限問題 因為每一個function都有 權限
是否要開啟Google登入驗證

##開啟
GOOGLE_AUTH_VALID=1

##關閉
GOOGLE_AUTH_VALID=0

### Swagger Commands
產生 swagger mode 範例: swagger:format
ex. php bin/hyperf.php swagger:format Post '{"errcode":0,"errmsg":"success","data":{"token":"666"}}' -P '/api/demo/indexPost'

產生 OpenApi json 格式資料: swagger:gen
ex. php bin/hyperf.php swagger:gen -f json
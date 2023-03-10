# api 文件

## api 列表
1. 廣告列表 - {{host}}/api/advertisement/list?site_id=1
參數 site_id 為歸屬網站

```JSMin
{
  "errcode": 0,
  "timestamp": 1667186259,
  "data": [
    {
      "id": 1,
      "name": "test", //廣告名稱
      "image_url": "\/advertisement\/avatar.png", // 廣告圖片
      "url": "www.google.com.tw", //廣告點選連結網址
      "position": 1, //廣告位置：1.上 banner、2.下 banner、3. 彈窗
      "start_time": "2022-10-20 00:00:00", //開始時間
      "end_time": "2022-11-20 00:00:00", //結束時間
      "buyer": "提姆", // 廣告購買人
      "expire": 0, // 上下架，前端無需判斷
      "site_id": 1, // 歸屬網站
      "created_at": "2022-10-26 17:10:10",
      "updated_at": "2022-10-26 17:10:10"
    }
  ]
}
```

2. 跑馬燈列表 - {{host}}/api/news_ticker/list?site_id=1
參數 site_id 為歸屬網站

```JSMin
{
  "errcode": 0,
  "timestamp": 1667186269,
  "data": [
    {
      "id": 1,
      "name": "test", //跑馬燈名稱
      "detail": "testtest", //跑馬燈內容
      "start_time": "2022-10-20 00:00:00", // 開始時間
      "end_time": "2022-11-20 00:00:00", // 結束時間
      "buyer": "提姆", //跑馬燈購買人
      "expire": 0, // 上下架，前端無需判斷
      "site_id": 1,  // 歸屬網站
      "created_at": "2022-10-28 12:15:59",
      "updated_at": "2022-10-28 12:15:59"
    }
  ]
}
```

3. seo關鍵字 {{host}}/api/seo/keywords?site_id=1
參數 site_id 為歸屬網站

```JSMin
{
  "errcode": 0,
  "timestamp": 1667186275,
  "data": {
    "keywords": "二次元、自拍、美女主播、摄像头、福利姬、抖音网红、小说影视、制服、按摩、逼哩逼哩、变装、野外、情趣内衣、小秘书、诱惑、同城交友、人妻绿帽、微博女神、重口味、小红书" // seo 關鍵字
  }
}
```

4. 歸屬網站 {{host}}/api/site/list

```JSMin
{
  "errcode": 0,
  "timestamp": 1667186283,
  "data": [
    {
      "id": 1,
      "name": "test", //網站名稱
      "url": "www.google.com", //網站網址
      "created_at": "2022-10-27 17:58:35",
      "updated_at": "2022-10-27 17:58:35",
      "deleted_at": null
    },
    {
      "id": 2,
      "name": "test2",
      "url": "www.google2.com",
      "created_at": "2022-10-27 17:58:35",
      "updated_at": "2022-10-27 17:58:35",
      "deleted_at": null
    }
  ]
}
```

5. 用戶活躍階段 {{host}}/api/visitor_activity/visit?site_id=1
參數 site_id 為歸屬網站，用戶停留在網站五秒後執行這個 api

```JSMin
{
  "errcode": 0,
  "timestamp": 1667186290,
  "data": {
    "status": true //是否成功紀錄
  }
}
```

6. 入口圖標 {{host}}/api/icon/list?site_id=1
   參數 site_id 為歸屬網站

```JSMin
{
  "errcode": 0,
  "timestamp": 1667199680,
  "data": [
    {
      "id": 1,
      "name": "test", //入口圖標名稱
      "image_url": "\/advertisement\/avatar.png", // 入口圖標圖片
      "url": "www.google.com.tw", //入口圖標點選連結網址
      "position": 1, //入口圖標位置：1.站點總站 2.精品推薦
      "sort": 1, //排序(由左自右由上自下，數字越小越前面，最小為0，最大為225)
      "start_time": "2022-10-20 00:00:00", //開始時間
      "end_time": "2022-11-20 00:00:00", //結束時間
      "buyer": "提姆", // 入口圖標購買人
      "expire": 0, // 上下架，前端無需判斷
      "site_id": 1, // 歸屬網站
      "created_at": "2022-10-26 17:10:10",
      "updated_at": "2022-10-26 17:10:10"
    }
  ]
}
```
7. 圖標統計  {{host}}/api/icon_count/click?site_id=1&&icon_id=1

   參數 site_id 為歸屬網站

   參數 icon_id 圖標ID

```JSMin
{
    "errcode": 0,
    "timestamp": 1667268070,
    "data": {
        "response_code": 200,
        "msg": "ok"
    }
}
```

8. 是否完成分享 {{host}}/api/share/status?share_id=1

   參數 share_id 為分享 id

```JSMin
{
  "errcode": 0,
  "timestamp": 1667357389,
  "data": {
    "status": 1, //0:未完成 1.已完成
    "count": 3 //目前已完成數
  }
}
```

9. 前端生成分享網址 {{host}}/api/share/getUri?site_id=1&fingerprint=067e68b1d25f0ce2b0f9224c3a01814b

   參數 site_id 為網站  id

   參數 fingerprint 為使用者唯一碼

```JSMin
{
    "errcode": 0,
    "timestamp": 1667375385,
    "data": {
        "code": 200,
        "msg": "ok",        
        "share_code": "9ddabad0e59b1d6aa47e013f8ca9cddb",
        "clickUri": "http://localhost:1677/api/share/click?site_id=1&share_code=9ddab"  //API 的URL 
    }
}
```


10. 分享網址點擊 {{host}}/api/share/click?site_id=1&share_code=9ddabad0e59b1d6aa47e013f8ca9cddb

   參數 site_id 為網站  id

   參數 share_code 為分享代碼

   會導回到落地頁    
   

11. 廣告點擊 {{host}}/api/advertisement_count/click?site_id=1&advertisements_id=1

  方法:GET
  傳入參數:
    參數 site_id 為網站 id (非必填，沒帶值預設為1)
    參數 advertisements_id 為廣告 id (必填)

  傳出:
```JSMin
{
    "errcode": 0,
    "timestamp": 1668067444,
    "data": {
        "msg": "OK"
    }
}
```
  錯誤代碼(errcode不為0及為有錯)： 
    errcode 401 =>  傳入資料缺少欄位
    errcode 402 =>  新增資料失敗
    errcode 403 =>  該日期的同網站且同廣告已有相同IP資料


## api 流程

1. 先執行 歸屬網站 {{host}}/api/site/list 取得目前網址對應的 id
2. 用對應的 id 去執行對應的 api ， ex: 廣告列表 - {{host}}/api/advertisement/list?site_id=1

## api 網址

http://172.104.46.27:9501
ex: http://172.104.46.27:9501/api/advertisement/list?site_id=1

## 圖片網址
http://172.104.46.27:9501
ex: http://172.104.46.27:9501/advertisement/c924eb5cc2f6f351969c779f998c60b95cefa498.jpg
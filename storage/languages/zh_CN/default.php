<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'titles' => [
        'role' => '角色',
        'roles' => '角色',
        'user' => '使用者',
        'manager' => '管理者',
        'advertisement' => '廣告',
        'tag' => '標籤',
        'image' => '圖片',
        'member' => '會員',
    ],
    'user_name' => '使用者名稱',
    'list' => '列表',
    'index' => '列表',
    'store' => '儲存',
    'create' => '新增',
    'edit' => '編輯',
    'delete' => '刪除',
    'expire' => '狀態',

    // -------------------------------------------------------------------
    // default
    'id' => '序號',
    'name' => '名稱',
    'content' => '內容',
    'image' => '圖片',
    'image_profile_dec' => '圖片(不上傳就不更新，只接受圖片檔案(png jpeg gif))',
    'start_time' => '開始時間',
    'end_time' => '結束時間',
    'buyer' => '購買人',
    'buyer_msg' => '請輸入廣告購買人名稱',
    'attribution_web' => '歸屬網站',
    'unattribution_web' => '無法歸屬',
    'action' => '動作',
    'pre_page' => '上一頁',
    'next_page' => '下一頁',
    'submit' => '送出',
    'take_up' => '上架',
    'take_down' => '下架',
    'take_msg' => '上下架',
    'take_up_down_msg' => '上架需在有效時間內才有用，下架是任意時間都有用',
    'take_up_down_info' => '上下架情況(任意時間均可下架，上架需在結束時間以前)',
    'choose_file' => '選擇檔案',
    'place' => '位置',
    'account' => '帳號',
    'enable_user' => '啟用',
    'account_def' => 'name',
    'pass_def' => 'password',
    'web_id' => '網站序號',
    'web_name' => '網站名稱',
    'web_url' => '網址',
    'web_name_def' => '請輸入網站名稱',
    'web_url_def' => '請輸入網址',
    'web_connect_url' => '連結網址',
    'sort' => '排序',
    'sort_msg' => '排序(由左自右由上自下，數字越小越前面，最小為0，最大為225)',
    'status' => '狀態',
    'status_one' => '未完成',
    'status_second' => '已完成',
    'change_status_fail' => '改為未完成',
    'change_status_true' => '改為已完成',
    'ip' => 'IP位址',
    'ip_msg_def' => '請輸入ip',
    'table_page_info' => '顯示第 :page 頁 共 :total 筆 共 :last_page 頁 每頁顯示 :step 筆',
    'remind' => '請小心操作',
    'click_time' => '點擊時間',
    'click_count' => '點擊數',
    'googleAuth'=> 'Googlg Auth 驗證 ',
    'role'=> '角色',
    'isopen' => ' GOOGLE AUTH驗證',
    'id_msg_def' => '請輸入id',
    'description_msg_def' => '請輸入描述',
    'name_msg_def' => '請輸入名稱',
    'sex' => '性別',
    'created_at' => "建立時間",
    'user_id' => "建立者",
    'type' => "類型",
    'preview' => "預覽",
    // -------------------------------------------------------------------
    // video
    'video' => [
        'insert' => '新增',
        'title' => '影片',
        'name' => '演員名',
        'role' => '角色管理',
        'tag' => '標籤',
        'category' => '分類',
        'is_free' => '是否限免',
        'is_hide' => '隐藏',
        'm3u8' => 'M3u8',
        'fan_id' => '番号',
        'update_video' => '更新影片',
        'cover_thumb' => '封面圖',
        'coins' => '定價',
        'actors' => '演員',
        'input_tags' => '標籤  請以,分開',
        'input_actors' => '演員  請以,分開',
        'gif_thumb' => 'GIF',
        'status' => '狀態',
        'status_type' => [
            0 => '未審核',
            1 => '審核通過',
            2 => '未通過',
            3 => '回調中',
            4 => '已刪除',
        ],
        'start_duration' => '最小時長',
        'end_duration' => '最大時長',
        'release_time' => '上架時間',
        'like' => '點讚數',
    ],

    // actor
    'actor' => [
        'insert' => '新增',
        'title' => '演員',
        'name' => '演員名',
        'role' => '角色管理',
        'advertisement' => '廣告管理',
        'tag' => '標籤管理',
        'image' => '圖片管理',
        'order' => '訂單管理',
    ],
    // left box
    'leftbox' => [
        'tittle' => '入口網站後台控制',
        'manager' => '使用者管理',
        'role' => '角色管理',
        'advertisement' => '廣告管理',
        'tag' => '標籤管理',
        'image' => '圖片管理',
        'order' => '訂單管理',
        'video' => '影片管理',
        'actor' => '演員管理',
        'product' => '商品管理',
        'member' => '會員管理',
        'classification' => '演員分類管理',
        'tagGroup' => '標籤群組管理',
        'announcement' => '公告管理',
        'memberLevel' => '會員等級管理',
        'image_group' => '套圖管理',
    ],
    // -------------------------------------------------------------------
    // UserController
    'error_login_msg' => '帳密有誤，請重新登入！',
    // -------------------------------------------------------------------
    // ManagerController
    'manager_control' => [
        'manager_control' => '管理者',
        'manager_insert' => '新增管理者',
        'manager_update' => '更新管理者',
        'manager_acc' => '管理者帳號',
        'manager_pass' => '密碼',
        'manager_sex' => '性別',
        'manager_age' => '年齡',
        'manager_avatar' => '大頭照',
        'manager_email' => '電子郵件',
        'manager_phone' => '手機',
        'manager_status' => '狀態',
        'GoogleAtuh'=> 'GOOGLE 驗證碼',
    ],
    'member_control' => [
        'member_control' => '會員',
        'member_insert' => '新增會員',
        'member_update' => '更新會員',
        'member_acc' => '會員帳號',
        'member_pass' => '密碼',
        'member_sex' => '性別',
        'member_age' => '年齡',
        'member_avatar' => '大頭照',
        'member_email' => '電子郵件',
        'member_phone' => '手機',
        'member_status' => '狀態',
    ],
    // -------------------------------------------------------------------
    // RoleController
    'role_control' => [
        'role' => '角色',
        'role_insert' => '新增角色',
        'role_update' => '更新角色',
        'role_name' => '角色名稱',
        'role_type' => '角色種類',
        'role_permission' => '角色權限',
        'role_type_name' => [
            'admin' => '後台',
            'api' => '前台'
        ]
    ],
    // -------------------------------------------------------------------
    // AdvertisementController
    'ad_control' => [
        'ad_control' => '廣告管理',
        'ad_insert' => '新增廣告',
        'ad_update' => '更新廣告',
        'ad_place' => '廣告位置',
        'ad_connect_url' => '連結網址',
        'ad_def_connect_url' => 'www.google.com.tw',
        'ad_input_name' => '請輸入廣告名稱',
        'ad_banner_up' => '上 banner',
        'ad_banner_down' => '下 banner',
        'ad_banner_pop' => '彈窗 banner',
        'ad_id' => '廣告序號',
        'ad_name' => '廣告名稱',
        'ad_image' => '圖片廣告',
        'ad_link' => '友情鏈接',
        'ad_position' => [
            \App\Model\Advertisement::POSITION['top_banner'] => '上 banner',
            \App\Model\Advertisement::POSITION['bottom_banner'] => '下 banner',
            \App\Model\Advertisement::POSITION['popup_window'] => '彈窗',
            \App\Model\Advertisement::POSITION['ad_image'] => '圖片廣告',
            \App\Model\Advertisement::POSITION['ad_link'] => '友情鏈接',
        ]
    ],
    // -------------------------------------------------------------------
    // TagController
    'tag_control' => [
        'tag_control' => '標籤管理',
        'tag_insert' => '新增標籤',
        'tag_name' => '標籤名稱',
    ],
    // -------------------------------------------------------------------
    // -------------------------------------------------------------------
    // TagController
    'announcement_control' => [
        'announcement_control' => '公告管理',
        'announcement_insert' => '新增公告',
        'announcement_update' => '更新公告',
        'announcement_title' => '公告標題',
        'announcement_content' => '公告內容',
        'announcement_start_time' => '公告上架時間',
        'announcement_end_time' => '公告下架時間',
        'announcement_status' => '公告狀態',
        'announcement_status_type' => [
            0 => '未啟用',
            1 => '啟用',
        ],
    ],
    // -------------------------------------------------------------------
    // ImageController
    'image_control' => [
        'image_control' => '圖片管理',
        'image_insert' => '新增圖片',
        'image_update' => '圖片更新',
        'image_name' => '圖片名稱',
        'image_thumbnail' => '圖片縮圖',
        'image_url' => '圖片網址',
        'image_likes' => '圖片按讚數',
        'image_group_id' => '套圖 id',
        'image_description' => '圖片描述',
    ],
    'image_group_control' => [
        'image_group_control' => '套圖管理',
        'image_group_insert' => '新增套圖',
        'image_group_update' => '套圖更新',
        'image_group_name' => '套圖名稱',
        'image_group_thumbnail' => '套圖封面圖縮圖',
        'image_group_url' => '套圖封面圖網址',
        'image_group_description' => '套圖描述',
    ],
    // -------------------------------------------------------------------
    // OrderController
    'order_control' => [
        'order_control' => '訂單管理',
        'order_insert' => '新增訂單',
        'order_num' => '訂單編號',
        'order_status' => '訂單狀態',
        'order_create_time' => '訂單成立時間',
        'order_edit' => '編輯訂單',
        'order_buyer_email' => 'email',
        'order_buyer_telephone' => '手機',
        'order_status_create' => '訂單成立',
        'order_status_delete' => '訂單取消',
        'order_status_finish' => '訂單完成',
        'order_price' => '訂單金額',
        'order_details' => '訂單明細',
        'order_search_msg' => '訂單編號或訂單狀態請擇一，如兩者都選，則以訂單編號為主',
        'order_choose_status' => '選擇訂單狀態',
        'order_status_msg' => [
            \App\Model\Order::ORDER_STATUS['create'] => '訂單成立',
            \App\Model\Order::ORDER_STATUS['delete'] => '訂單取消',
            \App\Model\Order::ORDER_STATUS['finish'] => '訂單完成'
        ]
    ],
    // -------------------------------------------------------------------
    // ProductController
    'product_control' => [
        'product_control' => '商品管理',
        'product_create' => '新增商品',
        'product_multiple_create' => '新增大批商品',
        'multiple_create' => '大批新增',
        'product_currency' => '商品幣別',
        'product_price' => '商品價格',
        'product_type' => '商品類型',
        'product_choose' => '選擇商品',
        'product_choose_type' => '選擇商品類型',
        'product_name' => '商品名稱',
        'product_search' => '商品查詢',
        'product_name_search' => '查詢名稱',
        'product_edit' => '編輯商品',
        'product_multiple_edit' => '編輯大批商品',
        'product_clear_choose' => '清除選擇',
        'product_num' => '商品數',
        'product_type_array' => [
            \App\Model\Product::TYPE_CORRESPOND_LIST['image'] => '圖片',
            \App\Model\Product::TYPE_CORRESPOND_LIST['video'] => '影片',
        ],
        'product_type_msg' => [
            'image' => '圖片',
            'video' => '影片',
        ]
    ],
    // -------------------------------------------------------------------
    // ActorClassificationController
    'actor_classification_control' => [
        'classification_control' => '演員分類管理',
        'classification_create' => '新增分類',
        'classification_name' => '分類名稱',
        'classification_name_def' => '請輸入分類名稱',
        'classification_sort_def' => '請輸入排序號碼',
        'classification_edit' => '編輯分類',
    ],
    // -------------------------------------------------------------------
    // TagGroupController
    'tag_group_control' => [
        'tag_group_control' => '標籤群組管理',
        'tag_group_hide' => '是否隱藏',
        'tag_group_insert' => '新增標籤群組',
        'tag_group_edit' => '編輯標籤群組',
        'hide' => '隱藏',
        'not_hide' => '顯示',
        'tag_group_name' => '標籤群組名稱',
    ],
    // -------------------------------------------------------------------
    // MemberLevelController
    'member_level_control' => [
        'member_level_control' => '會員等級管理',
        'member_level_insert' => '新增會員等級',
        'member_level_edit' => '編輯會員等級',
    ],
    // -------------------------------------------------------------------
];

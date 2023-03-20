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
    // -------------------------------------------------------------------
    // left box
    'leftbox' => [
        'tittle' => '入口網站後台控制',
        'manager' => '使用者管理',
        'role' => '角色管理',
        'advertisement' => '廣告管理',
        'tag' => '標籤管理',
        'image' => '圖片管理',
        'order' => '訂單管理',
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
    // -------------------------------------------------------------------
    // RoleController
    'role_control' => [
        'role' => '角色',
        'role_insert' => '新增角色',
        'role_update' => '更新角色',
        'role_name' => '角色名稱',
        'role_permission' => '角色權限',
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
        'ad_link' => '友情鏈接'
    ],
    // -------------------------------------------------------------------
    // TagController
    'tag_control' => [
        'tag_control' => '標籤管理',
        'tag_insert' => '新增標籤',
        'tag_name' => '標籤名稱',
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
        'image_group_id' => '圖片群組序號',
        'image_description' => '圖片描述',
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
        'order_choose_status' => '選擇訂單狀態'
    ],
    // -------------------------------------------------------------------
];

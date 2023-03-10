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
        'advertisementcount' => '廣告統計',
        'seo' => 'SEO',
        'usersite' => '用戶對應多站',
        'icon' => '圖標',
        'iconcount' => '圖標統計',
        'share' => '分享',
        'sharecount' => '分享統計',
        'visitoractivity' => '用戶活躍數圖表',
        'newsticker' => '跑馬燈管理',
        'site' => '多站管理',
        'retentionrate' => '用戶留存率',
    ],

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
    'daily_active_users' => '每日用戶活躍數',
    'daily_hit_users' => '每日用戶活躍數',
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
    // -------------------------------------------------------------------
    // left box
    'leftbox' => [
        'tittle' => '入口網站後台控制',
        'manager' => '使用者管理',
        'role' => '角色管理',
        'site' => '多站管理',
        'user_site' => '用戶對應多站管理',
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
    // SiteController
    'site_control' => [
        'site_control' => '多站管理',
        'site_insert' => '新增網站',
        'site_update' => '更新網站',
    ],
    // -------------------------------------------------------------------
    // UserSiteController
    'usersite_control' => [
        'usersite_control' => '用戶對應多站管理',
        'usersite_insert' => '新增用戶對應多站',
        'usersite_update' => '更新用戶對應多站',
        'user_id' => '用戶序號',
        'user_name' => '用戶名稱',
        'user_id_def' => '請輸入用戶序號',
    ],
    // -------------------------------------------------------------------
];

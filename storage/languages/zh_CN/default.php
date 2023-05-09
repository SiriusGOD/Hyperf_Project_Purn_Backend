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
        'actor' => '演员',
        'order' => '订单',
        'product' => '产品',
        'video' => '影片',
        'imagegroup'=> '套图',
        'memberlevel'=> '员会等级',
        'announcement'=> '公告',
        'user' => '使用者',
        'manager' => '管理者',
        'advertisement' => '广告',
        'tag' => '标籤',
        'image' => '图片',
        'member' => '会员',
        'redeem' => '兑换卷',
        'taggroup'=>'标籤群组管理',
        'actorclassification'=>'操作日誌',
        'userstep'=>'操作日誌',
        'customer_service' => '客服系统',
    ],
    'user_name' => '使用者名称',
    'list' => '列表',
    'index' => '列表',
    'store' => '储存',
    'create' => '新增',
    'edit' => '编辑',
    'detail' => '详细资料',
    'delete' => '删除',
    'expire' => '状态',

    // -------------------------------------------------------------------
    // default
    'id' => '序号',
    'name' => '名称',
    'content' => '内容',
    'image' => '图片',
    'image_profile_dec' => '图片(不上传就不更新，只接受图片档案(png jpeg gif))',
    'start_time' => '开始时间',
    'end_time' => '结束时间',
    'edit_time' => '编辑时间',
    'time' => '时间',
    'start' => '开始',
    'end' => '结束',
    'buyer' => '购买人',
    'buyer_msg' => '请输入广告购买人名称',
    'attribution_web' => '归属网站',
    'unattribution_web' => '无法归属',
    'action' => '动作',
    'pre_page' => '上一页',
    'next_page' => '下一页',
    'submit' => '送出',
    'take_up' => '上架',
    'take_down' => '下架',
    'take_msg' => '上下架',
    'take_up_down_msg' => '上架需在有效时间内才有用，下架是任意时间都有用',
    'take_up_down_info' => '上下架情况(任意时间均可下架，上架需在结束时间以前)',
    'choose_file' => '选择档案',
    'place' => '位置',
    'account' => '帐号',
    'enable_user' => '启用',
    'account_def' => 'name',
    'pass_def' => 'password',
    'web_id' => '网站序号',
    'web_name' => '网站名称',
    'web_url' => '网址',
    'web_name_def' => '请输入网站名称',
    'web_url_def' => '请输入网址',
    'web_connect_url' => '连结网址',
    'sort' => '排序',
    'sort_msg' => '排序(由左自右由上自下，数字越小越前面，最小为0，最大为225)',
    'status' => '状态',
    'status_one' => '未完成',
    'status_second' => '已完成',
    'change_status_fail' => '改为未完成',
    'change_status_true' => '改为已完成',
    'ip' => 'IP位址',
    'ip_msg_def' => '请输入ip',
    'table_page_info' => '显示第 :page 页 共 :total 笔 共 :last_page 页 每页显示 :step 笔',
    'remind' => '请小心操作',
    'click_time' => '点击时间',
    'click_count' => '点击数',
    'googleAuth'=> 'Googlg Auth 验证 ',
    'role'=> '角色',
    'isopen' => ' GOOGLE AUTH验证',
    'id_msg_def' => '请输入id',
    'description_msg_def' => '请输入描述',
    'name_msg_def' => '请输入名称',
    'sex' => '性别',
    'created_at' => "建立时间",
    'user_id' => "建立者",
    'type' => "类型",
    'preview' => "预览",
    'click_num' => "点击次数",
    'default_categorization_name' => '未整理分类',
    'default_all_categorization_name' => '所有分类',
    // -------------------------------------------------------------------
    // video
    'video' => [
        'insert' => '新增',
        'title' => '影片',
        'name' => '演员名',
        'role' => '角色管理',
        'tag' => '标籤',
        'category' => '分类',
        'is_free' => '是否限免',
        'is_hide' => '隐藏',
        'm3u8' => 'M3u8',
        'fan_id' => '番号',
        'update_video' => '更新影片',
        'cover_thumb' => '封面图',
        'coins' => '定价',
        'actors' => '演员',
        'input_tags' => '标籤  请以,分开',
        'input_actors' => '演员  请以,分开',
        'gif_thumb' => 'GIF',
        'status' => '状态',
        'status_type' => [
            0 => '未审核',
            1 => '审核通过',
            2 => '未通过',
            3 => '回调中',
            4 => '已删除',
        ],
        'start_duration' => '最小时长',
        'end_duration' => '最大时长',
        'release_time' => '上架时间',
        'like' => '点赞数',
        'click' => '观看次数',
        'pay_type' => '影片付费方式',
        'pay_type_types' => [
            0 => '免费',
            1 => 'vip',
            2 => '鑽石',
        ],
        'hot_order' => '大家都在看排序',
        'hot_order_desc' => '0 不排序，越小越前面',
    ],

    'withdraw'=>[
      'title'=>'提現列表',
      'pass'=>'審核通過',
      'cancel'=>'取消申請',
      'money'=>'提現金額'
    ],

    'user-step'=>[
        'index' => '列表',
        'username' => '管理者',
        'action' => '操作',
        'comment' => '说明',
        'title' => '操作日誌'
    ],
    // redeem
    'redeem' => [
        'insert' => '新增优惠卷',
        'edit' => '编辑优惠卷',
        'delete' => '停用',
        'title' => '优惠卷',
        'category' => '兑换分类',
        'content' => '兑换内容',
        'code' => '兑换代码',
        'count' => '可兑换次数',
        'counted' => '己兑换次数',
        'status_type' => [
            0 => '可用',
            1 => '不可用',
        ],
        'category_name' => '兑换卷分类',
        'status' => '装态',
        'categories' => [
            1 => 'VIP天数',
            2 => '鑽石点数',
            3 => '免费观看次数',
        ],
        'end_time' => '结束时间',
        'start_time' => '开始时间',
    ],
    // proxy
    'proxy' => [
        'title' => '代理',
        'proxy1' => '1级代理',
        'proxy2' => '2级代理',
        'proxy3' => '3级代理',
        'proxy4' => '4级代理',
        'name' => '代理人名',
        'order_amount' => '订单金额',
        'reach_amount' => '返佣金额',
    ],
    // actor
    'actor' => [
        'insert' => '新增',
        'title' => '演员',
        'name' => '演员名',
        'role' => '角色管理',
        'advertisement' => '广告管理',
        'tag' => '标籤管理',
        'image' => '图片管理',
        'order' => '订单管理',
    ],
    // left box
    'leftbox' => [
        'withdraw' =>'提現管理',
        'tittle' => '入口网站后台控制',
        'manager' => '使用者管理',
        'role' => '角色管理',
        'advertisement' => '广告管理',
        'tag' => '标籤管理',
        'image' => '图片管理',
        'order' => '订单管理',
        'video' => '影片管理',
        'actor' => '演员管理',
        'product' => '商品管理',
        'member' => '会员管理',
        'classification' => '演员分类管理',
        'tagGroup' => '标籤群组管理',
        'announcement' => '公告管理',
        'memberLevel' => '会员等级管理',
        'image_group' => '套图管理',
        'redeem' => '优惠卷管理',
        'coin' => '点数管理',
        'user-step'=>'操作日誌',
        'activity' => '用户日誌',
        'customer_service' => '客服系统',
        'pay' => '支付管理',
        'proxy' => '代理管理',
        'navigation' => '导航管理',
    ],
    // -------------------------------------------------------------------
    // UserController
    'error_login_msg' => '帐密有误，请重新登入！',
    // -------------------------------------------------------------------
    // ManagerController
    'manager_control' => [
        'manager_control' => '管理者',
        'manager_insert' => '新增管理者',
        'manager_update' => '更新管理者',
        'manager_acc' => '管理者帐号',
        'manager_pass' => '密码',
        'manager_sex' => '性别',
        'manager_age' => '年龄',
        'manager_avatar' => '大头照',
        'manager_email' => '电子邮件',
        'manager_phone' => '手机',
        'manager_status' => '状态',
        'GoogleAtuh'=> 'GOOGLE 验证码',
    ],
    // -------------------------------------------------------------------
    // member_control
    'member_control' => [
        'member_control' => '会员',
        'member_insert' => '新增会员',
        'member_update' => '更新会员',
        'member_acc' => '会员帐号',
        'member_pass' => '密码',
        'member_sex' => '性别',
        'member_age' => '年龄',
        'member_avatar' => '大头照',
        'member_email' => '电子邮件',
        'member_phone' => '手机',
        'member_status' => '状态',
        'member_level' => '会员等级',
        'member_coin' => '现金点数',
        'member_diamond_coins' => '鑽石点数',
        'member_diamond_quota' => '鑽石观看次数',
        'member_vip_quota' => 'VIP观看次数',
        'member_free_quota' => '免费观看次数',
        'member_free_quota_limit' => '免费观看次数上限',
        'member_level_start' => '会员等级起始时间',
        'member_level_end' => '会员等级结束时间',
    ],
    // -------------------------------------------------------------------
    // RoleController
    'role_control' => [
        'role' => '角色',
        'role_insert' => '新增角色',
        'role_update' => '更新角色',
        'role_name' => '角色名称',
        'role_type' => '角色种类',
        'role_permission' => '角色权限',
        'role_type_name' => [
            'admin' => '后台',
            'api' => '前台'
        ]
    ],
    // -------------------------------------------------------------------
    // AdvertisementController
    'ad_control' => [
        'ad_control' => '广告管理',
        'ad_insert' => '新增广告',
        'ad_update' => '更新广告',
        'ad_place' => '广告位置',
        'ad_connect_url' => '连结网址',
        'ad_def_connect_url' => 'www.google.com.tw',
        'ad_input_name' => '请输入广告名称',
        'ad_banner' => 'banner',
        'ad_banner_pop' => '弹窗 banner',
        'ad_id' => '广告序号',
        'ad_name' => '广告名称',
        'ad_image' => '图片广告',
        'ad_full' => '满版广告',
        'ad_position' => [
            \App\Model\Advertisement::POSITION['banner'] => 'banner',
            \App\Model\Advertisement::POSITION['popup_window'] => '弹窗 banner',
            \App\Model\Advertisement::POSITION['ad_image'] => '图片广告',
            \App\Model\Advertisement::POSITION['ad_full'] => '满版广告',
        ]
    ],
    // -------------------------------------------------------------------
    // TagController
    'tag_control' => [
        'tag_control' => '标籤管理',
        'tag_insert' => '新增标籤',
        'tag_edit' => '编辑标籤',
        'tag_name' => '标籤名称',
        'tag_hot_order' => '热门标籤排序',
        'tag_hot_order_desc' => '0 不排序，越小越前面'
    ],
    // -------------------------------------------------------------------
    // -------------------------------------------------------------------
    // TagController
    'announcement_control' => [
        'announcement_control' => '公告管理',
        'announcement_insert' => '新增公告',
        'announcement_update' => '更新公告',
        'announcement_title' => '公告标题',
        'announcement_content' => '公告内容',
        'announcement_start_time' => '公告上架时间',
        'announcement_end_time' => '公告下架时间',
        'announcement_status' => '公告状态',
        'announcement_status_type' => [
            0 => '未启用',
            1 => '启用',
        ],
    ],
    // -------------------------------------------------------------------
    // ImageController
    'image_control' => [
        'image_control' => '图片管理',
        'image_insert' => '新增图片',
        'image_update' => '图片更新',
        'image_name' => '图片名称',
        'image_thumbnail' => '图片缩图',
        'image_url' => '图片网址',
        'image_likes' => '图片按赞数',
        'image_clicks' => '图片观看次数',
        'image_group_id' => '套图 id',
        'image_description' => '图片描述',
    ],
    'image_group_control' => [
        'image_group_control' => '套图管理',
        'image_group_insert' => '新增套图',
        'image_group_update' => '套图更新',
        'image_group_name' => '套图名称',
        'image_group_thumbnail' => '套图封面图缩图',
        'image_group_url' => '套图封面图网址',
        'image_group_description' => '套图描述',
        'image_group_clicks' => '套图观看次数',
        'image_group_likes' => '套图按赞数',
        'image_group_pay_type' => '套图付费方式',
        'image_group_sync' => '套图同步',
        'image_group_pay_type_types' => [
            0 => '免费',
            1 => 'vip',
            2 => '鑽石'
        ],
        'image_group_hot_order' => '大家都在看排序',
        'image_group_hot_order_desc' => ' 0 不排序，越小越前面',
    ],
    // -------------------------------------------------------------------
    // OrderController
    'order_control' => [
        'order_control' => '订单管理',
        'order_insert' => '新增订单',
        'order_num' => '订单编号',
        'order_status' => '订单状态',
        'order_create_time' => '订单成立时间',
        'order_edit' => '编辑订单',
        'order_buyer_email' => 'email',
        'order_buyer_telephone' => '手机',
        'order_status_create' => '订单成立',
        'order_status_delete' => '订单取消',
        'order_status_finish' => '订单完成',
        'order_status_failure' => '订单付款失败',
        'order_price' => '订单金额',
        'order_details' => '订单明细',
        'order_search_msg' => '订单编号或订单状态请择一，如两者都选，则以订单编号为主',
        'order_choose_status' => '选择订单状态',
        'order_status_msg' => [
            \App\Model\Order::ORDER_STATUS['create'] => '订单成立',
            \App\Model\Order::ORDER_STATUS['delete'] => '订单取消',
            \App\Model\Order::ORDER_STATUS['finish'] => '订单完成',
            \App\Model\Order::ORDER_STATUS['failure'] => '订单付款失败',
        ],
        'order_status_fronted_msg' => [
            \App\Model\Order::ORDER_STATUS['create'] => '等待支付中',
            \App\Model\Order::ORDER_STATUS['delete'] => '订单取消',
            \App\Model\Order::ORDER_STATUS['finish'] => '成功',
            \App\Model\Order::ORDER_STATUS['failure'] => '失败',
        ],
    ],
    // -------------------------------------------------------------------
    // ProductController
    'product_control' => [
        'product_control' => '商品管理',
        'product_create' => '新增商品',
        'product_multiple_create' => '新增大批商品',
        'multiple_create' => '大批新增',
        'multiple_edit' => '大批修改',
        'product_currency' => '商品币别',
        'product_price' => '商品价格',
        'product_type' => '商品类型',
        'product_choose' => '选择商品',
        'product_choose_type' => '选择商品类型',
        'product_name' => '商品名称',
        'product_search' => '商品查询',
        'product_name_search' => '查询名称',
        'product_edit' => '编辑商品',
        'product_multiple_edit' => '编辑大批商品',
        'product_clear_choose' => '清除选择',
        'product_num' => '商品数',
        'product_type_array' => [
            \App\Model\Product::TYPE_CORRESPOND_LIST['image'] => '图片',
            \App\Model\Product::TYPE_CORRESPOND_LIST['video'] => '影片',
            \App\Model\Product::TYPE_CORRESPOND_LIST['member'] => '会员',
            \App\Model\Product::TYPE_CORRESPOND_LIST['points'] => '点数',
        ],
        'product_type_msg' => [
            \App\Model\ImageGroup::class => '图片',
            \App\Model\Video::class => '影片',
            \App\Model\MemberLevel::class => '会员',
            \App\Model\Coin::class => '点数',
        ],
        'product_type_name' => [
            'image' => '图片',
            'video' => '影片',
            'member' => '会员',
            'points' => '点数',
        ],
        'product_currency_msg' => [
            'CNY' => '人民币',
            'COIN' => '现金点数',
            'TWD' => '台币'
        ],
        'product_origin_type' => [
            0 => '免费',
            1 => 'VIP',
            2 => '鑽石'
        ],
        'product_id' => '商品序号',
    ],
    // -------------------------------------------------------------------
    // ActorClassificationController
    'actor_classification_control' => [
        'classification_control' => '演员分类管理',
        'classification_create' => '新增分类',
        'classification_name' => '分类名称',
        'classification_name_def' => '请输入分类名称',
        'classification_sort_def' => '请输入排序号码',
        'classification_edit' => '编辑分类',
    ],
    // -------------------------------------------------------------------
    // TagGroupController
    'tag_group_control' => [
        'tag_group_control' => '标籤群组管理',
        'tag_group_hide' => '是否隐藏',
        'tag_group_insert' => '新增标籤群组',
        'tag_group_edit' => '编辑标籤群组',
        'hide' => '隐藏',
        'not_hide' => '显示',
        'tag_group_name' => '标籤群组名称',
    ],
    // -------------------------------------------------------------------
    // MemberLevelController
    'member_level_control' => [
        'member_level_control' => '会员等级管理',
        'member_level_insert' => '新增会员等级',
        'member_level_edit' => '编辑会员等级',
        'member_level_duration' => '持续天数',
        'member_level_title' => '会员卡资讯',
        'member_level_description' => '会员卡描述',
        'member_level_remark' => '会员卡备註',
        'member_level_type' => [
            'vip' => \App\Model\MemberLevel::TYPE_NAME['vip'],
            'diamond' => \App\Model\MemberLevel::TYPE_NAME['diamond'],
        ],
    ],
    // -------------------------------------------------------------------
    // CoinController
    'coin_control' => [
        'coin_control' => '点数管理',
        'coin_insert' => '新增点数类别',
        'coin_edit' => '编辑点数类别',
        'points' => '点数',
        'points_msg' => '点数  (请勿随意变更点数！！！！)',
        'bonus' => '赠与鑽石',
        'bonus_msg' => '赠与鑽石  (请勿随意变更赠与鑽石!!!)',
        'coin_type' => [
            'cash' => \App\Model\Coin::TYPE_NAME['cash'],
            'diamond' => \App\Model\Coin::TYPE_NAME['diamond'],
        ],
    ],
    // -------------------------------------------------------------------
    // ActivityController
    'activity_control' => [
        'activity_control' => '用户日誌',
        'activity_last_activity' => '最后活动时间',
        'activity_device_type' => '设备类型',
        'activity_version' => '版本号',
        'activity_ip' => '用户 ip',
    ],
    // -------------------------------------------------------------------
    // CustomerServiceController
    'customer_service_control' => [
        'customer_service_control' => '客服系统',
        'customer_service_member_name' => '用户名称',
        'customer_service_type' => '问题种类',
        'customer_service_title' => '标题',
        'customer_service_updated_at' => '更新时间',
        'customer_service_type_array' => [
            1 => '客服问题',
        ],
    ],
    'customer_service_detail_control' => [
        'message_record' => '对话纪录',
        'message_response' => '回复讯息',
        'created_at' => '建立时间',
        'user_name' => '客服名称',
        'member_name' => '用户名称',
        'message' => '讯息',
        'image_url' => '图片',
    ],
    // -------------------------------------------------------------------
    // PayController
    'pay_control' => [
        'pay_control' => '支付管理',
        'pay_insert_control' => '新增支付方式',
        'pay_edit_control' => '编辑支付方式',
        'pay_pronoun' => '代称',
        'pay_open' => '开启',
        'pay_close' => '关闭',
        'pay_method' => '支付方式',
    ],
    // -------------------------------------------------------------------
    // PayController
    'navigation_control' => [
        'navigation_control' => '导航管理',
        'navigation_edit' => '编辑导航',
        'navigation_name' => '名称',
        'navigation_hot_order' => '排序',
    ],
    // -------------------------------------------------------------------
];


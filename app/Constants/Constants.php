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
namespace App\Constants;

class Constants
{
    public const TOKEN = 'token';

    public const USER_PERMISSION_KEY = 'USER_PERMISSION:';

    public const PERMISSION_DENIED = 'Permission denied';

    // 系统超级管理员id
    public const SYS_ADMIN_ID = 1;

    // Default Page
    public const DEFAULT_PAGE_PER = 10;

    public const SORT_BY = [
        'click' => 1,
        'created_time' => 2,
    ];

    public const DEFAULT_ACTOR = [
        'id' => 0,
        'user_id' => 1,
        'sex' => 0,
        'name' => '未分類',
        'avatar' => "",
        'created_at' => "2023-03-29 10:50:04",
        'updated_at' => "2023-03-29 10:50:04",
    ];
}

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

    //Default Page
    public const DEFAULT_PAGE_PER = 10;
}

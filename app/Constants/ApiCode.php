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

class ApiCode
{
    public const OK = 200;

    public const FOUND = 302;

    public const BAD_REQUEST = 400;

    // 傳入資料缺少欄位
    public const BAD_MISS_VARIABLE = 401;

    // 新增資料失敗
    public const BAD_INSERT_DB = 402;

    // 未登入
    public const BAD_LOGIN = 403;

    public const FATAL_ERROR = 500;
}

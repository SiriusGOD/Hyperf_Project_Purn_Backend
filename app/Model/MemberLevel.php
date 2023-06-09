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
namespace App\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $name
 * @property int $duration
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MemberLevel extends Model
{
    public const PAGE_PER = 10;

    public const TYPE_LIST = ['vip', 'diamond'];

    public const TYPE_NAME = [
        'vip' => 'VIP',
        'diamond' => '鑽石',
    ];

    public const TYPE_VALUE = [
        'vip' => 1,
        'diamond' => 2,
    ];

    public const NO_MEMBER_LEVEL = 0;

    public const ADD_QUOTA = 1;

    // 體驗卡1天的觀看數限制
    public const LIMIT_QUOTA = 50;

    public const ZERO_QUOTA = 0;

    // 永久
    public const FOREVER_DURATION = 3650;

    public const FOREVER = '永久';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'member_levels';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'duration' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

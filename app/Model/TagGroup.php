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

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $is_hide
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TagGroup extends Model
{
    // 每頁筆數
    public const PAGE_PER = 10;

    public const HIDE_LIST = ['顯示', '隱藏'];

    public const HIDE = [
        'not_hide' => 0,
        'hide' => 1,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'tag_groups';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'is_hide' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

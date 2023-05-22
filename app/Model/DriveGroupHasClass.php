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
 * @property int $drive_class_id
 * @property int $drive_group_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DriveGroupHasClass extends Model
{
    // 每頁筆數
    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'drive_group_has_class';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'drive_class_id' => 'integer', 'drive_group_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

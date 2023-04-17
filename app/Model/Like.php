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
 * @property string $type
 * @property int $type_id
 * @property int $count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Like extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'likes';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'type_id' => 'integer', 'count' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

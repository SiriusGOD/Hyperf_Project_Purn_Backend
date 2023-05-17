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
 * @property int $tag_id
 * @property int $popular_tag_id
 * @property string $popular_tag_name
 * @property int $popular_tag_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TagPopular extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'tag_populars';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'tag_id' => 'integer', 'popular_tag_id' => 'integer', 'popular_tag_count' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

}

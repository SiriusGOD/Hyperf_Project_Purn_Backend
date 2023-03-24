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
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $thumbnail
 * @property string $url
 * @property int $length
 * @property int $likes
 * @property string $refreshed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Video extends Model
{
    use SoftDeletes;

    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'videos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'length' => 'integer', 'likes' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

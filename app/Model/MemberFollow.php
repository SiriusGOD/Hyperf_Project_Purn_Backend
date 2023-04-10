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
 * @property int $member_id
 * @property string $correspond_type
 * @property int $correspond_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MemberFollow extends Model
{
    public const TYPE_LIST = ['image', 'video', 'actor', 'tag'];

    public const TYPE_CORRESPOND_LIST = [
        'image' => 'App\Model\Image',
        'video' => 'App\Model\Video',
        'actor' => 'App\Model\Actor',
        'tag' => 'App\Model\Tag',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'member_follows';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'correspond_id' => 'integer', 'member_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

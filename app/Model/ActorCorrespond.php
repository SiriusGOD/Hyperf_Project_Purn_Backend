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
 * @property string $type
 * @property int $correspond_id
 * @property int $actor_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ActorCorrespond extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'actor_corresponds';

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
    protected $casts = ['id' => 'integer', 'correspond_id' => 'integer', 'actor_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

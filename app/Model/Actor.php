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
 * @property int $sex
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Actor extends Model
{
    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'actors';

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
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'sex' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

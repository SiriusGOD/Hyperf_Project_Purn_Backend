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
 * @property string $name
 * @property string $pronoun
 * @property int $expire
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Pay extends Model
{
    public const PAGE_PER = 10;

    public const EXPIRE = ['no' => 0, 'yes' => 1];

    public const PROXY = ['online', 'agent'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'pays';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'name' => 'string', 'pronoun' => 'string', 'expire' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

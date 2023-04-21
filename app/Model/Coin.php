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
 * @property char $type
 * @property varchar $name
 * @property int $points
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Coin extends Model
{
    public const PAGE_PER = 10;

    public const TYPE_LIST = ['cash', 'diamond'];

    public const TYPE_NAME = [
        'cash' => '現金',
        'diamond' => '鑽石',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'coins';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'points' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

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
 * @property string $title 
 * @property string $code 
 * @property int $count 
 * @property int $counted 
 * @property int $category_id 
 * @property string $category_name 
 * @property int $diamond_point 
 * @property int $vip_days 
 * @property int $free_watch 
 * @property int $status 
 * @property string $start 
 * @property string $end 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $content 
 */
class Redeem extends Model
{
    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'redeems';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['code','status'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'counted' => 'integer', 'category_id' => 'integer', 'diamond_point' => 'integer', 'vip_days' => 'integer', 'free_watch' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

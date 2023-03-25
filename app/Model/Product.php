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
 * @property string $type
 * @property int $correspond_id
 * @property string $name
 * @property int $position
 * @property string $start_time
 * @property string $end_time
 * @property string $currency
 * @property string $selling_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Product extends Model
{
    public const EXPIRE = ['no' => 0, 'yes' => 1];
    public const PAGE_PER = 10;
    public const TYPE_LIST = [
        'image' => 'App\Model\Image',
        'video' => 'App\Model\Video'
    ];
    public const CURRENCY = [
        'CNY' => '人民幣',
        'USD' => '美金',
        'TWD' => '台幣'
    ];
    
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

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
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'correspond_id' => 'integer', 'position' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    // 影片關連
    public function video()
    {
        return $this->hasOne(Video::class, 'id', 'correspond_id');
    }
}

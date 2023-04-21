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
 * @property string $type
 * @property int $correspond_id
 * @property string $name
 * @property int $expire
 * @property string $start_time
 * @property string $end_time
 * @property string $currency
 * @property string $selling_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{
    public const EXPIRE = ['no' => 0, 'yes' => 1];

    public const PAGE_PER = 10;

    public const TYPE_LIST = ['image', 'video', 'member', 'points'];

    public const TYPE_LIST_NAME = [
        'image' => '圖片',
        'video' => '影片',
        'member' => '會員',
        'points' => '點數',
    ];

    public const TYPE_CORRESPOND_LIST = [
        'image' => 'App\Model\Image',
        'video' => 'App\Model\Video',
        'member' => 'App\Model\MemberLevel',
        'points' => 'App\Model\Coin',
    ];

    public const CURRENCY = ['CNY', 'COIN', 'DIAMOND'];

    public const CURRENCY_NAME = [
        'CNY' => '人民幣',
        'COIN' => '現金點數',
        'DIAMOND' => '鑽石點數',
    ];

    // 套圖 影片預設鑽石價錢
    public const DIAMOND_PRICE = 1;

    // 觀看次數
    public const QUOTA = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'products';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'correspond_id' => 'integer', 'position' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    // 影片關連
    public function video()
    {
        return $this->hasOne(Video::class, 'id', 'correspond_id');
    }
}

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
 * @property string $order_number
 * @property string $address
 * @property string $email
 * @property string $mobile
 * @property string $telephone
 * @property int $payment_type
 * @property string $currency
 * @property string $total_price
 * @property string $pay_way
 * @property string $pay_url
 * @property string $pay_proxy
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Order extends Model
{
    public const PAGE_PER = 10;

    public const ORDER_STATUS = ['create' => 1, 'delete' => 11, 'finish' => 21];

    public const PAY_WAY_MAP_NEW = [1 => 'wechat', 2 => 'bankcard', 3 => 'alipay', 4 => 'ecny', 5 => 'visa', 6 => 'agent'];

    public const PAY_WAY_TEXT = ['wechat' => '微信支付', 'bankcard' => '银联支付', 'alipay' => '支付宝支付', 'ecny' => '数字人民币支付', 'visa' => 'VISA支付', 'agent' => '商家代理支付'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'payment_type' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

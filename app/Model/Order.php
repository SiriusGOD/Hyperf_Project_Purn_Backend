<?php

declare (strict_types=1);
namespace App\Model;

use Carbon\Carbon;

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
 * @property int $status 
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Order extends Model
{
    public const PAGE_PER = 10;
    public const ORDER_STATUS = [
        'create' => 1,
        'delete' => 11,
        'finish' => 21
    ];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';
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
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'payment_type' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
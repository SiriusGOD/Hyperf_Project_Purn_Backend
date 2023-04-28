<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $member_id 
 * @property int $type 
 * @property string $uuid 
 * @property string $cash_id 
 * @property string $account 
 * @property string $account_name 
 * @property string $name 
 * @property string $amount 
 * @property string $trueto_amount 
 * @property int $status 
 * @property string $descp 
 * @property string $payed_at 
 * @property string $channel 
 * @property string $third_id 
 * @property string $order_desc 
 * @property string $coins 
 * @property int $withdraw_type 
 * @property int $withdraw_from 
 * @property string $ip 
 * @property string $address 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class MemberWithdraw extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_withdraw';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'type' => 'integer', 'status' => 'integer', 'withdraw_type' => 'integer', 'withdraw_from' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

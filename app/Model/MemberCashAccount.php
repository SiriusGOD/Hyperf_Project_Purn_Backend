<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $member_id 
 * @property int $type 
 * @property string $account_name 
 * @property string $account_number 
 * @property string $realname 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class MemberCashAccount extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_cash_account';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

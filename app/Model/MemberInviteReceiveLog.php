<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $member_id 
 * @property int $invite_by 
 * @property string $order_sn 
 * @property string $amount 
 * @property string $reach_amount 
 * @property int $level 
 * @property string $rate 
 * @property int $type 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class MemberInviteReceiveLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_invite_receive_log';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'invite_by' => 'integer', 'level' => 'integer', 'type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

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
    protected array $fillable = ['member_id', 'invite_by', 'reach_amount', 'amount', 'level', 'rate', 'type', 'order_sn'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'invite_by' => 'integer', 'level' => 'integer', 'type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * Get the member that the log belongs to.
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}

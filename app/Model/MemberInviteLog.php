<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $invited_by 
 * @property int $member_id 
 * @property int $level 
 * @property string $invited_code 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class MemberInviteLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_invite_log';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['invited_by', 'member_id' , 'level' ,'invited_code'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'invited_by' => 'integer', 'member_id' => 'integer', 'level' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

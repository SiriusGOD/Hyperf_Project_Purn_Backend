<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $member_id 
 * @property string $d_count 
 * @property string $k_count 
 * @property int $level 
 * @property int $differ_num 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class MemberInviteStart extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_invite_start';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'level' => 'integer', 'differ_num' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

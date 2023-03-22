<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $user_id 
 * @property int $tag_id 
 * @property int $count 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class UserInvitations extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_invitations';
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
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'use_date' => 'datetime', 'count' => 'integer'];
}
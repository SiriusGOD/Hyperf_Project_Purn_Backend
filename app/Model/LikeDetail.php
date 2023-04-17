<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $like_id 
 * @property int $member_id 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class LikeDetail extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'like_details';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'like_id' => 'integer', 'member_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
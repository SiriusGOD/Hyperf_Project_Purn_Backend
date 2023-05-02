<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $user_id 
 * @property string $name 
 * @property int $hot_order 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Navigation extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'navigations';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'hot_order' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

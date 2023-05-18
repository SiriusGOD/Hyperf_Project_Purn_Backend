<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $channel_id 
 * @property string $channel 
 * @property string $date 
 * @property int $hour 
 * @property string $pay_amount 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class ChannelAchievement extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'channel_achievements';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'channel_id' => 'integer', 'hour' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

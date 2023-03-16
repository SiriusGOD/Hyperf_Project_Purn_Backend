<?php

declare (strict_types=1);
namespace App\Model;

use Carbon\Carbon;

/**
 * @property int $id 
 * @property string $description 
 * @property string $param 
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SystemParam extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'system_params';
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
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
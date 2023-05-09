<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $member_id 
 * @property int $user_id 
 * @property string $model_type 
 * @property int $model_id 
 * @property string $content 
 * @property int $type 
 * @property int $status 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Report extends Model
{

    public const TYPE = [
        'hide' => 1,
        'report' => 2,
    ];

    public const STATUS = [
        'new' => 0,
        'pass' => 1,
        'no_pass' => 2,
    ];

    public const MODEL_TYPE = [
        'image_group'=> ImageGroup::class,
        'video' => Video::class,
    ];

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'reports';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'user_id' => 'integer', 'model_id' => 'integer', 'type' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

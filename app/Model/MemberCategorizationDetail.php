<?php

declare(strict_types=1);

namespace App\Model;



use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int $id 
 * @property int $member_categorization_id 
 * @property string $type 
 * @property int $type_id 
 * @property int $total_click 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class MemberCategorizationDetail extends Model
{
    use SoftDeletes;
    public const TYPES = [
        'video' => Video::class,
        'image_group' => ImageGroup::class,
    ];
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_categorization_details';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_categorization_id' => 'integer', 'type_id' => 'integer', 'total_click' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $member_id 
 * @property string $name 
 * @property int $hot_order 
 * @property int $is_default 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $is_first 
 */
class MemberCategorization extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_categorizations';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'hot_order' => 'integer', 'is_default' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'is_first' => 'integer'];

    public function memberCategorizationDetails()
    {
        return $this->hasMany(MemberCategorizationDetail::class);
    }
}

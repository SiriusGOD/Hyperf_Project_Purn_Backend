<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Model;

/**
 * @property int $id 
 * @property int $member_id 
 * @property int $type 
 * @property string $title 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property-read \Hyperf\Database\Model\Collection|CustomerServiceDetail[] $details 
 * @property-read Member $member 
 */
class CustomerService extends Model
{
    public const PAGE_PER = 10;

    public const TYPE = [
        'normal' => 1,
    ];

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'customer_services';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    protected array $appends = ['detail_count'];

    public function details()
    {
        return $this->hasMany(CustomerServiceDetail::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function lastUpdatedAt()
    {
        return $this->details()->orderByDesc('id')->first()->updated_at;
    }

    public function getDetailCountAttribute()
    {
        return $this->details()->where('is_read', 0)->count();
    }

    public function customerServiceCovers()
    {
        return $this->hasMany(CustomerServiceCover::class);
    }
}

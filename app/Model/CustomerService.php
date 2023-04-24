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
 * @property int $is_unread
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property CustomerServiceDetail[]|\Hyperf\Database\Model\Collection $details
 * @property Member $member
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
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'type' => 'integer', 'is_unread' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

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
}

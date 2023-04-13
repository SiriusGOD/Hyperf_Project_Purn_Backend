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
 * @property int $redeem_id
 * @property int $count
 * @property int $counted
 * @property string $redeem_code
 * @property int $redeem_category_id
 * @property string $start
 * @property string $end
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MemberRedeem extends Model
{
    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_redeems';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_id' => 'integer', 'redeem_id' => 'integer', 'count' => 'integer', 'counted' => 'integer', 'redeem_category_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

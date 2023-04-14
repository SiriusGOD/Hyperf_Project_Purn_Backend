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
 * @property int $member_redeem_id 
 * @property int $video_id 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $redeem_category_id 
 * @property int $member_id 
 */
class MemberRedeemVideo extends Model
{
    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'member_redeem_videos';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'member_redeem_id' => 'integer', 'video_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'redeem_category_id' => 'integer', 'member_id' => 'integer'];
}

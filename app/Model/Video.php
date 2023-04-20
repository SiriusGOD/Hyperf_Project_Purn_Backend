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

use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $description
 * @property string $refreshed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property int $type
 * @property string $fan_id
 * @property int $p_id
 * @property int $music_id
 * @property string $title
 * @property int $coins
 * @property string $m3u8
 * @property string $full_m3u8
 * @property string $v_ext
 * @property string $cover_thumb
 * @property int $thumb_width
 * @property int $thumb_height
 * @property string $gif_thumb
 * @property int $gif_width
 * @property int $gif_height
 * @property string $directors
 * @property string $actors
 * @property string $category
 * @property string $via
 * @property int $onshelf_tm
 * @property int $rating
 * @property int $refresh_at
 * @property int $is_free
 * @property int $comment
 * @property int $status
 * @property int $thumb_start_time
 * @property int $thumb_duration
 * @property int $is_hide
 * @property int $is_recommend
 * @property int $is_feature
 * @property int $is_top
 * @property int $count_pay
 * @property int $club_id
 * @property int $topic_id
 * @property int $duration
 * @property int $likes
 * @property string $release_time
 * @property User $user
 * @property \Hyperf\Database\Model\Collection|Tag[] $tags
 */
class Video extends Model
{
    use SoftDeletes;

    public const PAGE_PER = 10;

    public const VIDEO_TYPE = [
        'free' => 0,
        'vip' => 1,
        'diamond' => 2
    ];

    protected array $appends = ['model_type'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'videos';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'type' => 'integer', 'p_id' => 'integer', 'music_id' => 'integer', 'coins' => 'integer', 'thumb_width' => 'integer', 'thumb_height' => 'integer', 'gif_width' => 'integer', 'gif_height' => 'integer', 'onshelf_tm' => 'integer', 'rating' => 'integer', 'refresh_at' => 'integer', 'is_free' => 'integer', 'comment' => 'integer', 'status' => 'integer', 'thumb_start_time' => 'integer', 'thumb_duration' => 'integer', 'is_hide' => 'integer', 'is_recommend' => 'integer', 'is_feature' => 'integer', 'is_top' => 'integer', 'count_pay' => 'integer', 'club_id' => 'integer', 'topic_id' => 'integer', 'duration' => 'integer', 'likes' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'correspond', 'tag_corresponds', 'correspond_id');
    }

    protected function getModelTypeAttribute()
    {
        return self::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $user_id 
 * @property string $description 
 * @property string $refreshed_at 
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
 * @property string $tags 
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
 * @property int $hot_order 
 * @property int $mod 
 * @property string $_id 
 * @property string $sign 
 * @property int $category_id 
 * @property string $source 
 * @property string $cover_full 
 * @property int $cover_witdh 
 * @property int $cover_height 
 * @property int $total_click 
 * @property int $is_calc 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class ImportVideo extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'import_videos';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'type' => 'integer', 'p_id' => 'integer', 'music_id' => 'integer', 'coins' => 'integer', 'thumb_width' => 'integer', 'thumb_height' => 'integer', 'gif_width' => 'integer', 'gif_height' => 'integer', 'onshelf_tm' => 'integer', 'rating' => 'integer', 'refresh_at' => 'integer', 'is_free' => 'integer', 'comment' => 'integer', 'status' => 'integer', 'thumb_start_time' => 'integer', 'thumb_duration' => 'integer', 'is_hide' => 'integer', 'is_recommend' => 'integer', 'is_feature' => 'integer', 'is_top' => 'integer', 'count_pay' => 'integer', 'club_id' => 'integer', 'topic_id' => 'integer', 'duration' => 'integer', 'likes' => 'integer', 'hot_order' => 'integer', 'mod' => 'integer', 'category_id' => 'integer', 'cover_witdh' => 'integer', 'cover_height' => 'integer', 'total_click' => 'integer', 'is_calc' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

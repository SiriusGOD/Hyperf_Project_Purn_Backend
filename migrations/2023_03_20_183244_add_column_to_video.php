<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnToVideo extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
          $table->integer('type')->nullable()->comment('1 长横幅 2 短 竖图');
          $table->integer('fan_id')->nullable()->comment('番号或标识');
          $table->integer('p_id')->nullable()->comment('资源中心ID');
          $table->integer('uid')->nullable()->comment('用户编号');
          $table->integer('music_id')->nullable()->comment('音乐id');
          $table->string('title')->nullable()->comment('影片标题');
          $table->integer('coins')->nullable()->comment('定价');
          $table->string('m3u8')->nullable()->comment('影片资源或预览');
          $table->string('full_m3u8')->nullable()->comment('完整视频的m3u8地址');
          $table->string('v_ext')->nullable()->comment('视频格式类型');
          $table->string('duration')->nullable()->comment('时长，秒');
          $table->string('cover_thumb')->nullable()->comment('封面小图');
          $table->integer('thumb_width')->nullable()->comment('封面宽');
          $table->integer('thumb_height')->nullable()->comment('封面高');
          $table->string('gif_thumb')->nullable()->comment('视频动图');
          $table->integer('gif_width')->nullable()->comment('视频动图寬');
          $table->integer('gif_height')->nullable()->comment('视频动图高');
          $table->string('directors')->nullable()->comment('导演');
          $table->string('actors')->nullable()->comment('演员');
          $table->string('category')->nullable()->comment('类型 0 mv 1 av 2 ai 3 动漫 4  live 5 gay');
          $table->string('tags')->nullable()->comment('影片标签');
          $table->string('via')->nullable()->comment('来源');
          $table->integer('onshelf_tm')->nullable()->comment('影片上映时间');
          $table->integer('rating')->nullable()->comment('总历史点击数');
          $table->integer('refresh_at')->nullable()->comment('刷新时间');
          $table->integer('is_free')->nullable()->comment('是否限免 0 免费视频 1vip视频 2金币视频');
          $table->integer('like')->nullable()->comment('喜欢点击数');
          $table->integer('comment')->nullable()->comment('评论数');
          $table->integer('status')->nullable()->comment('0未审核1审核通过 2未通过 3 回调中 4 逻辑删除');
          $table->integer('thumb_start_time')->nullable()->comment('精彩片段开始时间');
          $table->integer('thumb_duration')->nullable()->comment('精彩时长：秒');
          $table->integer('is_hide')->nullable()->comment('0显示1隐藏');
          $table->integer('is_recommend')->nullable()->comment('是否推荐');
          $table->integer('is_feature')->nullable()->comment('是否是精选视频');
          $table->integer('is_top')->nullable()->comment('是否置顶');
          $table->integer('count_pay')->nullable()->comment('售卖次数');
          $table->integer('club_id')->nullable()->comment('粉丝团编号');
          $table->integer('topic_id')->nullable()->comment('tv合集');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
          $table->dropColumn('type');
          $table->dropColumn('fan_id');
          $table->dropColumn('p_id');
          $table->dropColumn('uid');
          $table->dropColumn('music_id');
          $table->dropColumn('title');
          $table->dropColumn('coins');
          $table->dropColumn('m3u8');
          $table->dropColumn('full_m3u8');
          $table->dropColumn('v_ext');
          $table->dropColumn('duration');
          $table->dropColumn('cover_thumb');
          $table->dropColumn('thumb_width');
          $table->dropColumn('thumb_height');
          $table->dropColumn('gif_thumb');
          $table->dropColumn('gif_width');
          $table->dropColumn('gif_height');
          $table->dropColumn('directors');
          $table->dropColumn('actors');
          $table->dropColumn('category');
          $table->dropColumn('tags');
          $table->dropColumn('via');
          $table->dropColumn('onshelf_tm');
          $table->dropColumn('rating');
          $table->dropColumn('refresh_at');
          $table->dropColumn('is_free');
          $table->dropColumn('like');
          $table->dropColumn('comment');
          $table->dropColumn('status');
          $table->dropColumn('thumb_start_time');
          $table->dropColumn('thumb_duration');
          $table->dropColumn('is_hide');
          $table->dropColumn('is_recommend');
          $table->dropColumn('is_feature');
          $table->dropColumn('is_top');
          $table->dropColumn('count_pay');
          $table->dropColumn('club_id');
          $table->dropColumn('topic_id');
        });
    }
}

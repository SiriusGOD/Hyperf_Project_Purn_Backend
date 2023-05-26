<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateImportVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('會員id');
            $table->text('description')->comment('影片描述');
            $table->datetime('refreshed_at')->comment('重新推送時間');
            $table->softDeletes();
            $table->integer('type')->nullable()->comment('1 长横幅 2 短 竖图');
            $table->string('fan_id')->nullable()->comment('番号或标识');
            $table->integer('p_id')->nullable()->comment('资源中心ID');
            $table->integer('music_id')->nullable()->comment('音乐id');
            $table->string('title')->nullable()->comment('影片标题');
            $table->integer('coins')->nullable()->comment('定价');
            $table->string('m3u8')->nullable()->comment('影片资源或预览');
            $table->string('full_m3u8')->nullable()->comment('完整视频的m3u8地址');
            $table->string('v_ext')->nullable()->comment('视频格式类型');
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
            $table->integer('duration')->nullable()->comment('時長');
            $table->integer('likes')->nullable()->comment('點讚數');
            $table->datetime('release_time')->useCurrent()->comment('上架時間');
            $table->integer('hot_order')->default(0)->comment('熱門搜尋排序，0為不排');
            $table->integer('mod')->default(0);
            $table->string('_id')->default('0');
            $table->string('sign')->default('0');
            $table->integer('category_id')->default(0);
            $table->string('source')->default('0')->comment('來源m3u8');
            $table->string('cover_full')->default('0')->comment('封面');
            $table->integer('cover_witdh')->nullable()->comment('封面寬');
            $table->integer('cover_height')->nullable()->comment('封面高');
            $table->bigInteger('total_click')->default(0)->comment('30天內總點擊數');
            $table->bigInteger('is_calc')->default(0)->comment('0:未算過  ,1:己算過 是否算過');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_videos');
    }
};

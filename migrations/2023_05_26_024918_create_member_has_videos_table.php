<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberHasVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_has_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->index()->comment('會員ID');
            $table->integer('video_id')->index()->comment('影片ID');
            $table->datetimes();
            $table->integer('member_has_video_category_id')->default(0)->index()->comment('分類ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_has_videos');
    }
};

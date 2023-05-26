<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberRedeemVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_redeem_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_redeem_id')->index()->comment('兌換ID');
            $table->integer('video_id')->index()->comment('影片ID');
            $table->datetimes();
            $table->integer('redeem_category_id')->comment('兌換分類的ID');
            $table->integer('member_id')->index()->comment('會員ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_redeem_videos');
    }
};

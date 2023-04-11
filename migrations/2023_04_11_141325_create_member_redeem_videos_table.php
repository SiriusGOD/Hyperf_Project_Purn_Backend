<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberRedeemVideosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_redeem_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_redeem_id')->comment('兌換ID')->index();
            $table->integer('video_id')->comment('影片ID')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_redeem_videos');
    }
}

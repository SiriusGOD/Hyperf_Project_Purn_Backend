<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class MemberHasVideos extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_has_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->index()->comment('會員ID');
            $table->integer('video_id')->index()->comment('影片ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_has_videos');
    }
}

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('會員id')->unsigned();
            $table->string('name')->comment('影片名稱');
            $table->string('thumbnail')->comment('影片縮圖');
            $table->string('url')->comment('影片網址');
            $table->integer('length')->comment('影片長度')->unsigned();
            $table->integer('likes')->comment('影片按讚數')->unsigned();
            $table->timestamp('refreshed_at')->comment('重新推送時間');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
}

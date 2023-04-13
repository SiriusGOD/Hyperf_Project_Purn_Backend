<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAnnouncementsTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('用戶id');
            $table->string('title')->comment('公告標題');
            $table->text('content')->comment('公告內容');
            $table->timestamp('start_time')->comment('啟用時間');
            $table->timestamp('end_time')->comment('結束時間');
            $table->tinyInteger('status')->comment('啟用狀態，0:未上架，1:已上架');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
}

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberHasVideoCategories extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_has_video_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->comment('類型 id');
            $table->string('name')->comment('分類名稱');
            $table->timestamps();
        });

        Schema::table('member_has_videos', function (Blueprint $table) {
            $table->integer('member_has_video_category_id')->default(0)->index()->comment("分類ID");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_has_video_categories');
    }
}

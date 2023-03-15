<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('會員id')->unsigned();
            $table->string('title')->comment('圖片標題');
            $table->string('title_thumbnail')->nullable()->comment('圖片標題縮圖');
            $table->string('thumbnail')->nullable()->comment('圖片縮圖');
            $table->string('url')->comment('圖片網址');
            $table->integer('likes')->comment('圖片按讚數')->unsigned();
            $table->integer('group_id')->comment('圖片群組')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
}

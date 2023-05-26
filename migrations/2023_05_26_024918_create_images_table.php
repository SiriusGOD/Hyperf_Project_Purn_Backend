<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('會員id');
            $table->string('title')->comment('圖片標題');
            $table->string('thumbnail')->nullable()->comment('圖片縮圖');
            $table->string('url')->comment('圖片網址');
            $table->text('description')->comment('圖片描述');
            $table->unsignedInteger('group_id')->comment('圖片群組');
            $table->datetimes();
            $table->softDeletes();
            $table->bigInteger('sync_id')->default(0)->comment('資源中心 id');
            $table->integer('thumbnail_height')->comment('縮圖高度');
            $table->integer('thumbnail_weight')->comment('縮圖寬度');
            $table->integer('height')->comment('圖片高度');
            $table->integer('weight')->comment('圖片寬度');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
};

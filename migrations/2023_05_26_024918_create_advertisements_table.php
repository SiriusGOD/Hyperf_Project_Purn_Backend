<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('建立者id');
            $table->string('name')->comment('廣告名稱');
            $table->string('image_url')->nullable()->comment('廣告圖片連結');
            $table->string('url')->comment('廣告連結');
            $table->unsignedInteger('position')->comment('廣告位置：1.上 banner、2.下 banner、3. 彈窗');
            $table->datetime('start_time')->comment('廣告上架時間');
            $table->datetime('end_time')->comment('廣告下架時間');
            $table->string('buyer')->comment('廣告購買人名稱');
            $table->unsignedInteger('expire')->default(0)->index()->comment('是否已過期:0.否、1.是');
            $table->datetimes();
            $table->bigInteger('height')->default(0)->comment('圖片高度');
            $table->bigInteger('weight')->default(0)->comment('圖片寬度');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisements');
    }
};

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('廣告名稱');
            $table->string('image_url')->comment('廣告圖片連結')->nullable();
            $table->string('url')->comment('廣告連結');
            $table->integer('position')->comment('廣告位置：1.上 banner、2.下 banner、3. 彈窗')->unsigned();
            $table->timestamp('start_time')->comment('廣告上架時間');
            $table->timestamp('end_time')->comment('廣告下架時間');
            $table->string('buyer')->comment('廣告購買人名稱');
            $table->integer('expire')->unsigned()->comment('是否已過期:0.否、1.是')->index()->default(0);
            $table->integer('site_id')->comment('站點id')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
}

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateIconsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('icons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('入口圖標名稱');
            $table->string('image_url')->comment('入口圖標圖片連結')->nullable();
            $table->string('url')->comment('入口圖標連結');
            $table->integer('position')->comment('入口圖標位置：1.站點總站、2.精品推薦')->unsigned();
            $table->integer('sort')->comment('排序由左自右由上自下，數字越小越前面，最小為0，最大為225')->unsigned()->default(0);
            $table->timestamp('start_time')->comment('入口圖標上架時間');
            $table->timestamp('end_time')->comment('入口圖標下架時間');
            $table->string('buyer')->comment('入口圖標購買人名稱');
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
        Schema::dropIfExists('icons');
    }
}

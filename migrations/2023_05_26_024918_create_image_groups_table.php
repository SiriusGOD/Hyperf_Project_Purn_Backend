<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateImageGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('管理員id');
            $table->string('title')->comment('套圖標題');
            $table->string('thumbnail')->nullable()->comment('套圖縮圖');
            $table->string('url')->comment('套圖圖片網址');
            $table->string('description')->comment('描述');
            $table->datetimes();
            $table->softDeletes();
            $table->tinyInteger('pay_type')->default(0)->comment('付費方式，0：免費，1：vip，2：鑽石');
            $table->integer('hot_order')->default(0)->comment('熱門搜尋排序，0為不排');
            $table->bigInteger('sync_id')->default(0)->comment('資源中心 id');
            $table->bigInteger('height')->nullable()->comment('圖片寬度');
            $table->bigInteger('weight')->nullable()->comment('圖片高度');
            $table->bigInteger('total_click')->default(0)->comment('30天內總點擊數');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('image_groups');
    }
};

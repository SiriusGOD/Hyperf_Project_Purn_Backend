<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateRedeemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redeems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->index()->comment('兌換名稱');
            $table->string('code')->unique()->comment('兌換代碼');
            $table->integer('count')->comment('兌換次數上限');
            $table->integer('counted')->default(0)->comment('己兌換次數');
            $table->integer('category_id')->index()->comment('分類ID , 1.VIP 天數,2.鑽石點數,3.免費觀看次數');
            $table->string('category_name', 100)->index()->comment('分類名稱');
            $table->integer('diamond_point')->default(0)->comment('鑽石點數');
            $table->integer('vip_days')->default(0)->comment('VIP天數');
            $table->integer('free_watch')->default(0)->comment('免費觀看次數');
            $table->integer('status')->default(0)->comment('是否停用 ,0:啟用,1:停用');
            $table->dateTime('start')->comment('兌換開始日期');
            $table->dateTime('end')->comment('兌換結束日期');
            $table->datetimes();
            $table->text('content')->comment('活動內容');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redeems');
    }
};

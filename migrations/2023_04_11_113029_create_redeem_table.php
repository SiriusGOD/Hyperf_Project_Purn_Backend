<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateRedeemTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('redeems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('兌換名稱')->index();
            $table->string('code')->comment('兌換代碼')->unique()->index();
            $table->integer('count')->comment('兌換次數上限');
            $table->integer('counted')->default(0)->comment('己兌換次數');
            $table->integer('category_id')->comment('分類ID , 1.VIP 天數,2.鑽石點數,3.免費觀看次數')->index();
            $table->string('category_name',100)->comment('分類名稱')->index();
            $table->integer('diamond_point')->default(0)->comment('鑽石點數');
            $table->integer('vip_days')->default(0)->comment('VIP天數');
            $table->integer('free_watch')->default(0)->comment('免費觀看次數');
            $table->integer('status')->default(0)->comment('是否停用 ,0:啟用,1:停用');
            $table->dateTime('start')->comment('兌換開始日期');
            $table->dateTime('end')->comment('兌換結束日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeems');
    }
}

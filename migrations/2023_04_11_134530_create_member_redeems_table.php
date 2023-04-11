<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberRedeemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_redeems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->comment('會員ID')->index();
            $table->integer('redeem_id')->comment('兌換ID')->index();
            $table->integer('count')->default(0)->comment('免費次數');
            $table->integer('counted')->default(0)->comment('己兌換次數');
            $table->string('redeem_code')->comment('兌換code');
            $table->integer('redeem_category_id')->comment('兌換分類ID')->index();
            $table->dateTime('start')->comment('兌換開始日期')->index();
            $table->dateTime('end')->comment('兌換結束日期')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_redeems');
    }
}

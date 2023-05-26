<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateBuyMemberLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy_member_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id')->index()->comment('前台用戶ID');
            $table->string('member_level_type')->index()->comment('會員卡類型');
            $table->unsignedBigInteger('member_level_id')->index()->comment('購買的會員等級ID');
            $table->string('order_number')->index()->comment('購買的訂單編號');
            $table->datetime('start_time')->comment('會員資格持續時間');
            $table->datetime('end_time')->comment('會員資格結束時間');
            $table->datetimes();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buy_member_levels');
    }
};

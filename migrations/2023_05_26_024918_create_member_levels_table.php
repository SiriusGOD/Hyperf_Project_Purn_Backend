<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('會員id');
            $table->char('type', 10)->comment('會員等級類型 vip 鑽石');
            $table->string('name', 50)->default('')->comment('名稱');
            $table->string('title')->nullable()->comment('會員卡資訊');
            $table->string('description')->nullable()->comment('會員卡描述');
            $table->string('remark')->nullable()->comment('會員卡備註');
            $table->integer('duration')->default(0)->comment('會員等級持續天數, 0表示永久');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_levels');
    }
};

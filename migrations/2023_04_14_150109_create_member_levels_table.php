<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberLevelsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('會員id')->unsigned();
            $table->char('type', 10)->comment('會員等級類型 vip 鑽石');
            $table->string('name', 50)->default('')->comment('名稱');
            $table->integer('duration')->default(0)->comment('會員等級持續天數, 0表示永久');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_levels');
    }
}

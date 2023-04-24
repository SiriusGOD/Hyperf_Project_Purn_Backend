<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->comment('用戶 id');
            $table->timestamp('last_activity')->comment('關閉 app 前時間');
            $table->string('device_type')->comment('設備類型');
            $table->string('version')->comment('版本號');
            $table->ipAddress('ip')->comment('ip 位址');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_activities');
    }
}

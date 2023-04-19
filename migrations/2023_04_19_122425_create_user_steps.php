<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserSteps extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_steps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_name')->comment('使用者名稱');
            $table->integer('user_id')->comment('使用者ID');
            $table->integer('role_id')->comment('使用者角色ID');
            $table->string('action')->comment('動作');
            $table->string('comment')->comment('動作說明');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_steps');
    }
}

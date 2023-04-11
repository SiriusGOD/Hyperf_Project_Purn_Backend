<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberVerificationsTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->comment('用戶id');
            $table->string('code')->comment('驗證碼');
            $table->timestamp('expired_at')->comment('過期時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_verifications');
    }
}

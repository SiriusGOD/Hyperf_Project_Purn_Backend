<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberFollowsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_follows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->unsigned()->index()->comment('前台用戶ID');
            $table->string('correspond_type')->comment('追蹤的Class 標籤 演員 影片');
            $table->bigInteger('correspond_id')->unsigned()->comment('追蹤的ID 標籤 演員 影片');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_follows');
    }
}

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_follows', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id')->index()->comment('前台用戶ID');
            $table->string('correspond_type')->comment('追蹤的Class 標籤 演員 影片');
            $table->unsignedBigInteger('correspond_id')->comment('追蹤的ID 標籤 演員 影片');
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
        Schema::dropIfExists('member_follows');
    }
};

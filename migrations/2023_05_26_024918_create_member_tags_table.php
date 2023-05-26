<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tag_id')->comment('標籤id');
            $table->bigInteger('count')->default(0)->comment('點擊次數');
            $table->datetimes();
            $table->bigInteger('member_id')->index()->comment('前台用戶id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_tags');
    }
};

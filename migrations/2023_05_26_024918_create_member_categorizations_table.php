<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberCategorizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_categorizations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->comment('會員 id');
            $table->string('name')->comment('分類名稱');
            $table->integer('hot_order')->comment('排序');
            $table->tinyInteger('is_default')->comment('是否為預設');
            $table->datetimes();
            $table->tinyInteger('is_first')->default(0)->comment('是否為一開始建立的分類');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_categorizations');
    }
};

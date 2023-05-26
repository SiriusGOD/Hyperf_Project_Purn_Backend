<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->comment('會員 id');
            $table->bigInteger('user_id')->nullable()->comment('管理者 id');
            $table->string('model_type')->comment('模型類型');
            $table->bigInteger('model_id')->comment('模型id');
            $table->string('content')->default('')->comment('檢舉或者隱藏內容');
            $table->tinyInteger('type')->comment('類型，1:隱藏，2:檢舉');
            $table->tinyInteger('status')->comment('狀態，0:未處理，1:通過，2:退回');
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
        Schema::dropIfExists('reports');
    }
};

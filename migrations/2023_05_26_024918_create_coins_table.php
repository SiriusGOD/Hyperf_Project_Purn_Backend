<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('會員id');
            $table->char('type', 10)->index()->comment('點數類型 現金 鑽石');
            $table->string('name', 50)->default('')->comment('名稱');
            $table->integer('points')->default(0)->comment('點數');
            $table->integer('bonus')->default(0)->comment('贈與點數');
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
        Schema::dropIfExists('coins');
    }
};

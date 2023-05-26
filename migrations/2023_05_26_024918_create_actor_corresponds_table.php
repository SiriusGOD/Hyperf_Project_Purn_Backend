<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateActorCorrespondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actor_corresponds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('correspond_type');
            $table->unsignedBigInteger('correspond_id');
            $table->unsignedBigInteger('actor_id');
            $table->datetimes();
            $table->bigInteger('total_click')->default(0)->comment('30天內總點擊數');
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
        Schema::dropIfExists('actor_corresponds');
    }
};

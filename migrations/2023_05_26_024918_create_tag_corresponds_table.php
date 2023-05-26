<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTagCorrespondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_corresponds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('correspond_type');
            $table->unsignedBigInteger('correspond_id');
            $table->unsignedBigInteger('tag_id')->index();
            $table->datetimes();
            $table->softDeletes();
            $table->bigInteger('total_click')->default(0)->comment('30天內總點擊數');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_corresponds');
    }
};

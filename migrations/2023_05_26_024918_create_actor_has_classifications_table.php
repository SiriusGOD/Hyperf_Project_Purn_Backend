<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateActorHasClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actor_has_classifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('actor_id')->index()->comment('演員ID');
            $table->integer('actor_classifications_id')->index()->comment('分類ID');
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
        Schema::dropIfExists('actor_has_classifications');
    }
};

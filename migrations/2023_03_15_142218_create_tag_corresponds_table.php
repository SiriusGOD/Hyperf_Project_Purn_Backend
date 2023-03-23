<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTagCorrespondsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tag_corresponds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->bigInteger('correspond_id')->unsigned();
            $table->bigInteger('tag_id')->unsigned()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_corresponds');
    }
}

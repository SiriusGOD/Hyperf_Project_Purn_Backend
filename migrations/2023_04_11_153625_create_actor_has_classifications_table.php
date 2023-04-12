<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateActorHasClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actor_has_classifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('actor_id')->index()->comment('演員ID');
            $table->integer('actor_classifications_id')->index()->comment('分類ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actor_has_classifications');
    }
}

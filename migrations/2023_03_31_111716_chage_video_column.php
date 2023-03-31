<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ChageVideoColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
          $table->dropColumn('duration');
        });
        Schema::table('videos', function (Blueprint $table) {
          $table->integer('duration')->nullable()->comment("時長");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('', function (Blueprint $table) {
          $table->string('duration')->nullable()->comment('時長')->change();
        });
    }
}

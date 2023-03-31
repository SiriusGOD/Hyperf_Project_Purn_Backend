<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class FixVideoColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
          $table->dropColumn('like');
        });
        Schema::table('videos', function (Blueprint $table) {
          $table->integer('likes')->nullable()->comment('點讚數');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
          $table->dropColumn('likes');
        });
        Schema::table('videos', function (Blueprint $table) {
          $table->integer('like')->nullable()->comment('點讚數');
        });
    }
}

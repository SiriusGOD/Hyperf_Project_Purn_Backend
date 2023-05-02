<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ChangeVideoC extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
          $table->renameColumn('cover_heigh', 'cover_height')->nullable()->comment('封面高')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}

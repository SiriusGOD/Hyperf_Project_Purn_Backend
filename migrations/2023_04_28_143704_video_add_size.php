<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class VideoAddSize extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('cover_witdh')->nullable()->comment('封面寬');
            $table->integer('cover_heigh')->nullable()->comment('封面高');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('cover_witdh');
            $table->dropColumn('cover_heigh');
        });
    }
}

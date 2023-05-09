<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddHeightWeightImages extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->integer('thumbnail_height')->comment('縮圖高度');
            $table->integer('thumbnail_weight')->comment('縮圖寬度');
            $table->integer('height')->comment('圖片高度');
            $table->integer('weight')->comment('圖片寬度');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('thumbnail_height');
            $table->dropColumn('thumbnail_weight');
            $table->dropColumn('height');
            $table->dropColumn('weight');
        });
    }
}

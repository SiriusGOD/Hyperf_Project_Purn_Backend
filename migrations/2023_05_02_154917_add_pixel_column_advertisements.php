<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddPixelColumnAdvertisements extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->bigInteger('height')->comment('圖片高度')->default(0);
            $table->bigInteger('weight')->comment('圖片寬度')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn('height');
            $table->dropColumn('weight');
        });
    }
}

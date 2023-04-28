<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddHighAndWeightImageGroups extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->bigInteger('height')->nullable()->comment('圖片寬度');
            $table->bigInteger('weight')->nullable()->comment('圖片高度');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->dropColumn('height');
            $table->dropColumn('weight');
        });
    }
}

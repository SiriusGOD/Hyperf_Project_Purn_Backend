<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddHotOrderColumnImageGroups extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->integer('hot_order')->comment('熱門搜尋排序，0為不排')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->dropColumn('hot_order');
        });
    }
}

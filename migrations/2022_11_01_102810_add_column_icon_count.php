<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnIconCount extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('icon_counts', function (Blueprint $table) {
            $table->integer('site_id')->comment('網站ID ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('icon_counts', function (Blueprint $table) {
            $table->dropColumn('site_id');
        });
    }
}

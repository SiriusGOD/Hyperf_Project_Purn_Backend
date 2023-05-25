<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ModifyBuyMemberLevelsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('buy_member_levels', function (Blueprint $table) {
            $table->datetime('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buy_member_levels', function (Blueprint $table) {
            $table->timestamp('end_time')->nullable()->change();
        });
    }
}

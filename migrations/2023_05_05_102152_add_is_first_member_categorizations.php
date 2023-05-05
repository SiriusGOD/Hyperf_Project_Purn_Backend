<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddIsFirstMemberCategorizations extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_categorizations', function (Blueprint $table) {
            $table->tinyInteger('is_first')->comment('是否為一開始建立的分類')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_categorizations', function (Blueprint $table) {
            $table->dropColumn('is_first');
        });
    }
}

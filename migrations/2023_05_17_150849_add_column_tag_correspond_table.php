<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnTagCorrespondTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tag_corresponds', function (Blueprint $table) {
            $table->softDeletes();
            $table->bigInteger('total_click')->comment('30天內總點擊數')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tag_corresponds', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropColumn('total_click');
        });
    }
}

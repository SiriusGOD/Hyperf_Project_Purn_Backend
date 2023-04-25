<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddSyncIdColumnImageGroups extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->bigInteger('sync_id')->comment('資源中心 id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->dropColumn('sync_id');
        });
    }
}

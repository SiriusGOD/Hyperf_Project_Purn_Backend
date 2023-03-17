<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ModifyActorCorrespondsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('actor_corresponds', function (Blueprint $table) {
            $table->renameColumn('type', 'correspond_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actor_corresponds', function (Blueprint $table) {
            $table->renameColumn('correspond_type', 'type');
        });
    }
}

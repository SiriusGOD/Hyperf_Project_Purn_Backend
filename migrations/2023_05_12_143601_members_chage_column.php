<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class MembersChageColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('aff');
        });
        Schema::table('members', function (Blueprint $table) {
            $table->string('aff')->default()->unique()->comment("邀請碼");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}

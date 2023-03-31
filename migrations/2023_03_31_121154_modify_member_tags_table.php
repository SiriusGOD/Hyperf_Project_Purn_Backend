<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ModifyMemberTagsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_tags', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->bigInteger('member_id')->comment('前台用戶id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_tags', function (Blueprint $table) {
            $table->bigInteger('user_id')->comment('用戶id')->index();
            $table->dropColumn('member_id');
        });
    }
}

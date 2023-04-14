<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ModifyColumnMemberTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('device', 10)->comment('會員使用設備 ios android web')->nullable();
            $table->string('register_ip', 40)->comment('註冊IP')->nullable();
            $table->string('last_ip', 40)->comment('最後登入IP')->nullable();
            $table->renameColumn('role_id', 'buy_level_id')->comment('購買會員等級ID')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('device');
            $table->dropColumn('register_ip');
            $table->dropColumn('last_ip');
            $table->renameColumn('buy_level_id', 'role_id')->comment('角色ID')->change();
        });
    }
}

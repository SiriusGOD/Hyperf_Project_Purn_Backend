<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ChangeColumnMemberTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('coins', 8, 2)->comment('現金點數')->default(0)->index();
            $table->decimal('diamond_coins', 8, 2)->comment('鑽石點數')->default(0)->index();
            $table->renameColumn('buy_level_id', 'member_level_status')->comment('0:沒有會員等級 1:VIP 2:鑽石')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('coins');
            $table->dropColumn('diamond_coins');
            $table->renameColumn('member_level_status', 'buy_level_id')->comment('購買會員等級ID')->change();
        });
    }
}

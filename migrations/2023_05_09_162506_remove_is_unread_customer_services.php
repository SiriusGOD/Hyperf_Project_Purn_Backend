<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class RemoveIsUnreadCustomerServices extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            $table->dropColumn('is_unread');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_services', function (Blueprint $table) {
            $table->tinyInteger('is_unread')->comment('用戶是否未讀');
        });
    }
}

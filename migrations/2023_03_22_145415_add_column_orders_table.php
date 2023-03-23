<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->string('pay_way')->comment('支付方式')->after('total_price');
            $table->string('pay_url')->comment('支付链接')->after('pay_way');
            $table->string('pay_proxy')->comment('online线上充值/agent代理充值')->after('pay_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('pay_way');
            $table->dropColumn('pay_url');
            $table->dropColumn('pay_proxy');
        });
    }
}

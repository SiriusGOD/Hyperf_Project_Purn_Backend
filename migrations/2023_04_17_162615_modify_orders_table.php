<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ModifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pay_order_id')->comment('產生支付鏈接後回傳的訂單編號')->default('')->index()->after('order_number');
            $table->string('pay_third_id')->comment('第三方訂單編號')->default('')->index()->after('pay_order_id');
            $table->decimal('pay_amount', 8, 2)->comment('實際付款金額')->nullable()->index()->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pay_order_id');
            $table->dropColumn('pay_third_id');
            $table->dropColumn('pay_amount');
        });
    }
}

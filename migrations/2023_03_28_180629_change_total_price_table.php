<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ChangeTotalPriceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Hyperf\DbConnection\Db::statement('ALTER TABLE orders CHANGE total_price total_price NUMERIC(8, 2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('total_price')->change();
        });
    }
}

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnCustomerServiceCoversTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_service_covers', function (Blueprint $table) {
            $table->integer('height')->comment('圖片長')->default(0)->after('url');
            $table->integer('width')->comment('圖片寬')->default(0)->after('height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_service_covers', function (Blueprint $table) {
            $table->dropColumn('height');
            $table->dropColumn('width');
        });
    }
}

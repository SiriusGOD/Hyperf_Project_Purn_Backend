<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddPayTypeColumnImageGroups extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->tinyInteger('pay_type')->default(0)->comment('付費方式，0：免費，1：vip，2：鑽石');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_groups', function (Blueprint $table) {
            $table->dropColumn('pay_type');
        });
    }
}

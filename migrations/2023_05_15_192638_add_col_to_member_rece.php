<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColToMemberRece extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_invite_receive_log', function (Blueprint $table) {
            $table->string("product_name")->default("")->comment("商品名稱");
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnMemberLevelTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_levels', function (Blueprint $table) {
            $table->string('title')->comment('會員卡資訊')->nullable()->after('name');
            $table->string('description')->comment('會員卡描述')->nullable()->after('title');
            $table->string('remark')->comment('會員卡備註')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_levels', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('description');
            $table->dropColumn('remark');
        });
    }
}

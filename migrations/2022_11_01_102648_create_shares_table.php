<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSharesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->comment('分享代碼');
            $table->ipAddress('ip')->comment('使用者 ip');
            $table->string('fingerprint')->comment('browser 唯一id')->nullable();
            $table->integer('status')->comment('達成狀況，0.未達成、1.已達成')->default(0);
            $table->integer('site_id')->comment('歸屬網站');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
}

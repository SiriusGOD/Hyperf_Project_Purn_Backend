<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateShareCountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('share_counts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('share_id')->unsigned()->comment('分享 id')->index();
            $table->ipAddress('ip')->comment('點擊者 ip');
            $table->date('click_date')->comment('點擊日期');
            $table->integer('site_id')->comment('站點id')->default(1)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_counts');
    }
}

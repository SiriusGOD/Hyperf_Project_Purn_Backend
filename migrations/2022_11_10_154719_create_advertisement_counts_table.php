<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAdvertisementCountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advertisement_counts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertisements_id')->unsigned()->comment('廣告 id')->index();
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
        Schema::dropIfExists('advertisement_counts');
    }
}

<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateClickDetailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('click_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('click_id')->comment('點擊次數 id');
            $table->bigInteger('member_id')->comment('點擊 user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('click_details');
    }
}

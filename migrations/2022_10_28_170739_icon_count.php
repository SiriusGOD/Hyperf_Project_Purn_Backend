<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class IconCount extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('icon_counts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date')->comment('日期');
            $table->integer('icon_id')->comment('icon id');
            $table->string('icon_name')->comment('icon 名稱');
            $table->integer('count')->comment('次數');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icon_counts');
    }
}

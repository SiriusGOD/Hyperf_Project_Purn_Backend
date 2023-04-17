<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateLikeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('like_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('like_id')->comment('按讚數 id');
            $table->bigInteger('member_id')->comment('按讚 user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('like_details');
    }
}

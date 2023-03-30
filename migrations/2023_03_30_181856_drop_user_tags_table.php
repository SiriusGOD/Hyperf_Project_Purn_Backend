<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class DropUserTagsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('user_tags');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('user_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('用戶id')->index();
            $table->bigInteger('tag_id')->comment('標籤id');
            $table->bigInteger('count')->default(0)->comment('點擊次數');
            $table->timestamps();
        });
    }
}

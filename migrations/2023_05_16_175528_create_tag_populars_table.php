<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTagPopularsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tag_populars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tag_id')->comment("標籤id")->index();
            $table->integer('popular_tag_id')->comment("該tag_id下，所有作品的top6標籤");
            $table->string('popular_tag_name')->comment("該tag_id下，所有作品的top6標籤名稱");
            $table->integer('popular_tag_count')->comment("該tag_id下，所有作品的top6標籤出現次數");
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_populars');
    }
}

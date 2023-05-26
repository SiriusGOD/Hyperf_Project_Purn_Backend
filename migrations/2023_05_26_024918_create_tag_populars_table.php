<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTagPopularsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_populars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tag_id')->index()->comment('標籤id');
            $table->integer('popular_tag_id')->comment('該tag_id下，所有作品的top6標籤');
            $table->string('popular_tag_name')->comment('該tag_id下，所有作品的top6標籤名稱');
            $table->integer('popular_tag_count')->comment('該tag_id下，所有作品的top6標籤出現次數');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_populars');
    }
};

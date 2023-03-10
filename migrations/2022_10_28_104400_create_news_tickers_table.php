<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateNewsTickersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_tickers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('   跑馬燈名稱');
            $table->string('detail')->comment('跑馬燈內容');
            $table->timestamp('start_time')->comment('跑馬燈上架時間');
            $table->timestamp('end_time')->comment('跑馬燈下架時間');
            $table->string('buyer')->comment('跑馬燈購買人名稱');
            $table->integer('expire')->unsigned()->comment('是否已過期:0.否、1.是')->index()->default(0);
            $table->integer('site_id')->comment('站點id')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_tickers');
    }
}

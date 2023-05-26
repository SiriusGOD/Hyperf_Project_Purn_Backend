<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateClicksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->comment('類型');
            $table->bigInteger('type_id')->comment('類型 id');
            $table->date('statistical_date')->index()->comment('統計日期');
            $table->bigInteger('count')->comment('點擊次數');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clicks');
    }
};

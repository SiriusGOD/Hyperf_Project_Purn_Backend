<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberCategorizationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_categorization_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_categorization_id')->comment('會員分類id');
            $table->string('type')->comment('模型種類');
            $table->integer('type_id')->comment('模型 id');
            $table->bigInteger('total_click')->comment('30天內點擊次數');
            $table->datetimes();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_categorization_details');
    }
};

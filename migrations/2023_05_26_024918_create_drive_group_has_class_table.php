<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDriveGroupHasClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drive_group_has_class', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('drive_class_id')->comment('類別ID');
            $table->integer('drive_group_id')->comment('群組ID');
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
        Schema::dropIfExists('drive_group_has_class');
    }
};

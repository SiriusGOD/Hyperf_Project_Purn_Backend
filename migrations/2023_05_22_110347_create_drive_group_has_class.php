<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDriveGroupHasGroups extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('drive_group_has_class', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('drive_class_id')->comment('類別ID');
            $table->integer('drive_group_id')->comment('群組ID');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_group_has_class');
    }
}

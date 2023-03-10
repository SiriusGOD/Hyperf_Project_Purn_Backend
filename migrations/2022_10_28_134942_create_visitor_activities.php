<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateVisitorActivities extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitor_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ipAddress('ip')->comment('使用者 ip');
            $table->integer('site_id')->comment('網站 id');
            $table->date('visit_date')->comment('造訪日期')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_activities');
    }
}

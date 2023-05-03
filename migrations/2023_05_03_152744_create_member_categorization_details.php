<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberCategorizationDetails extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_categorization_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_categorization_id')->comment('會員分類id');
            $table->string('type')->comment('模型種類');
            $table->integer('type_id')->comment('模型 id');
            $table->bigInteger('total_click')->comment('30天內點擊次數')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_categorization_details');
    }
}

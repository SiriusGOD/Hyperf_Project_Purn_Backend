<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class RemoveVideoFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('length');
            $table->dropColumn('likes');
            $table->dropColumn('name');
            $table->dropColumn('thumbnail');
            $table->dropColumn('uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('url')->comment('影片網址');
            $table->integer('length')->comment('影片長度')->unsigned();
            $table->integer('likes')->comment('影片按讚數')->unsigned();
            $table->integer('uid')->comment('使用者ID')->unsigned();
            $table->string('name')->comment('影片名稱');
            $table->string('thumbnail')->comment('影片縮圖');
        });
    }
}

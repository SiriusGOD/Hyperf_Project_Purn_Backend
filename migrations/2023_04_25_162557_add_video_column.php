<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddVideoColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('mod')->comment('')->default(0);
            $table->string('_id')->comment('')->default(0);
            $table->string('sign')->comment('')->default(0);
            $table->integer('category_id')->comment('')->default(0);
            $table->string('source')->comment('來源m3u8')->default(0);
            $table->string('cover_full')->comment('封面')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}

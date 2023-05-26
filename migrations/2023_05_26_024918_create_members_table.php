<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('name', 20)->default('')->comment('用户昵称');
            $table->string('account')->nullable()->comment('帳號');
            $table->char('password', 200)->default('')->comment('用户密码');
            $table->tinyInteger('sex')->default(0)->comment('性别');
            $table->integer('age')->default(0)->comment('年龄');
            $table->string('avatar')->default('')->comment('用户头像');
            $table->char('email')->nullable()->unique('email')->comment('用户邮箱');
            $table->char('phone')->nullable()->unique('phone')->comment('用户手机号');
            $table->tinyInteger('status')->default(1);
            $table->datetimes();
            $table->char('uuid', 30)->nullable()->default('')->comment('用戶 uuid');
            $table->integer('member_level_status')->comment('角色id');
            $table->string('device', 10)->nullable()->comment('會員使用設備 ios android web');
            $table->string('register_ip', 40)->nullable()->comment('註冊IP');
            $table->string('last_ip', 40)->nullable()->comment('最後登入IP');
            $table->decimal('coins')->default(0)->index()->comment('現金點數');
            $table->decimal('diamond_coins')->default(0)->index()->comment('鑽石點數');
            $table->integer('diamond_quota')->nullable()->default(0)->comment('鑽石觀看次數 購買鑽石會員卡1天才會獲得');
            $table->integer('vip_quota')->nullable()->default(0)->comment('VIP觀看次數 購買VIP會員卡1天才會獲得');
            $table->integer('free_quota')->default(0)->comment('免費觀看次數');
            $table->integer('free_quota_limit')->default(1)->comment('免費次數上線');
            $table->string('aff')->default('')->comment('邀请码md5( md5(uuid) )');
            $table->integer('invited_by')->default(0)->comment('被谁 aff 邀请');
            $table->integer('invited_num')->default(0)->comment('已邀请安装个数');
            $table->decimal('tui_coins', 10)->default(0)->comment('推广收入');
            $table->decimal('total_tui_coins', 10)->default(0)->comment('累计推广收入');
            $table->string('aff_url')->default('')->comment('渠道');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
};

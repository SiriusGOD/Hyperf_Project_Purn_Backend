<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\DbConnection\Db;

// 新增 基本權限
class RoleSeed implements BaseInterface
{
    public function up(): void
    {
        $roles = [
            'superadmin',
            'ad-user',
        ];

        $permissions = new \App\Model\Permission();
        $permissions = $permissions->all();

        foreach ($roles as $name) {
            $modelRole = new \App\Model\Role();
            $modelRole->name = $name;
            $modelRole->save();
            foreach ($permissions as $row) {
                if ($name == 'ad-user') {
                    if ($row->main == 'advertisings') {
                        Db::table('role_has_permissions')->insert(['role_id' => $modelRole->id, 'permission_id' => $row->id]);
                    }
                } else {
                    Db::table('role_has_permissions')->insert(['role_id' => $modelRole->id, 'permission_id' => $row->id]);
                }
            }
        }
    }

    public function down(): void
    {
        \App\Model\Role::truncate();
    }

    public function base(): bool
    {
        return true;
    }
}

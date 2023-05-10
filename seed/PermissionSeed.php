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
class PermissionSeed implements BaseInterface
{
    public function up(): void
    {
        $permissions = [
            # 角色
            'roles' => [
                'role-index',
                'role-create',
                'role-edit',
                'role-delete',
                'role-store',
            ],
            # 演員
            'actors' => [
                'actor-index',
                'actor-create',
                'actor-edit',
                'actor-delete',
                'actor-store',
            ],
            # 後台管理員
            'manager' => [
                'manager-index',
                'manager-create',
                'manager-edit',
                'manager-delete',
                'manager-store',
                'manager-googleAuth'
            ],
            # 廣告管理
            'advertisement' => [
                'advertisement-index',
                'advertisement-create',
                'advertisement-edit',
                'advertisement-expire',
                'advertisement-store',
            ],
            #現提 
            'withdraw' => [
                'withdraw-index',
                'withdraw-detail',
                'withdraw-pass',
                'withdraw-cancel',
                'withdraw-set',
            ],
        ];

        foreach ($permissions as $main => $permission) {
            foreach ($permission as $name) {
                $model = new \App\Model\Permission();
                $model->main = $main;
                $model->name = $name;
                $model->save();
            }
        }
    }

    public function down(): void
    {
        \App\Model\Permission::truncate();
    }

    public function base(): bool
    {
        return true;
    }
}

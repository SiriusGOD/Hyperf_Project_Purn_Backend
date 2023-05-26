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
class PermissionRedeemSeed implements BaseInterface
{
    public function up(): void
    {
        $permissions = [
            # 兌換卷管理
            'redeem' => [
                'redeem-index',
                'redeem-create',
                'redeem-list',
                'redeem-delete'
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
        \App\Model\Permission::where('main', 'tag')
            ->delete();
    }

    public function base(): bool
    {
        return false;
    }
}

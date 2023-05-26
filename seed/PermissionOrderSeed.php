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
class PermissionOrderSeed implements BaseInterface
{
    public function up(): void
    {
        $permissions = [
            # 標籤管理
            'order' => [
                'order-index',
                'order-create',
                'order-edit',
                'order-delete',
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

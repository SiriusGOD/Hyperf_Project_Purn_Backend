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
class PermissionActivitySeed implements BaseInterface
{
    public function up(): void
    {
        $permissions = [
            # 用戶日誌
            'activity' => [
                'activity-index',
                'activity-create',
                'activity-edit',
                'activity-delete',
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
        \App\Model\Permission::where('main', 'activity')
            ->delete();
    }

    public function base(): bool
    {
        return true;
    }
}

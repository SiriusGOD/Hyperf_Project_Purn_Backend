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
class Permission20221115Seed implements BaseInterface
{
    public function up(): void
    {
        $permissions = [
            # 用戶活躍數圖表管理
            'retentionrate' => [
                'retentionrate-list',
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
        \App\Model\Permission::where('main', 'Retentionrate')
            ->where('name', 'Retentionrate-index')
            ->delete();
    }

    public function base(): bool
    {
        return true;
    }
}

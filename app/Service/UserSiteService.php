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
namespace App\Service;

use App\Model\UserSite;

class UserSiteService
{
    public function storeUserSite(int $id, int $userId, int $siteId): void
    {
        $model = UserSite::findOrNew($id);
        $model->user_id = $userId;
        $model->site_id = $siteId;
        $model->save();
    }
}

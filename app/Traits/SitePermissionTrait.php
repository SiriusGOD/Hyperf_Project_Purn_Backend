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
namespace App\Traits;

use App\Model\Role;
use App\Model\Site;
use App\Model\UserSite;

trait SitePermissionTrait
{
    public function attachQueryBuilder($query)
    {
        $siteIds = $this->getSiteIds();

        if (! empty($siteIds)) {
            $query = $query->whereIn('site_id', $siteIds);
        }

        return $query;
    }

    // 只用在驗證器檢驗
    public function getSiteIds(): array
    {
        $user = auth('session')->user();

        $siteIds = [];
        if ($user->role_id == Role::SUPER_ADMIN) {
            $sites = Site::all();
            foreach ($sites as $site) {
                $siteIds[] = $site->id;
            }
            return $siteIds;
        }
        $query = UserSite::select('*');
        $query = $query->where('user_id', $user->id);
        $userSites = $query->get();

        foreach ($userSites as $userSite) {
            $siteIds[] = $userSite->site_id;
        }

        return $siteIds;
    }

    // 只用在 view siteSelect.blade.php
    public function getSiteModels()
    {
        $siteIds = $this->getSiteIds();

        return Site::whereIn('id', $siteIds)->get();
    }
}

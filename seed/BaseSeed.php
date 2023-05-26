<?php

declare(strict_types=1);

use HyperfExt\Hashing\Hash;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class BaseSeed implements BaseInterface
{
    public function up(): void
    {
        \Hyperf\Support\make(ImageGroupSeed::class)->up();
        \Hyperf\Support\make(ImportTagSeed::class)->up();
        \Hyperf\Support\make(ImportImageTagSeed::class)->up();
        \Hyperf\Support\make(CoinSeed::class)->up();
        \Hyperf\Support\make(MemberLevelSeed::class)->up();
        \Hyperf\Support\make(NavigationSeed::class)->up();
        \Hyperf\Support\make(PaySeed::class)->up();
        \Hyperf\Support\make(ProductSeed::class)->up();
        \Hyperf\Support\make(PayCorrespondSeed::class)->up();
        \Hyperf\Support\make(DriveGroupSeed::class)->up();
        \Hyperf\Support\make(ActorClassificationSeed::class)->up();
        \Hyperf\Support\make(ActorSeed::class)->up();
        \Hyperf\Support\make(PermissionAnnouncementSeed::class)->up();
        \Hyperf\Support\make(PermissionClassGroupSeed::class)->up();
        \Hyperf\Support\make(PermissionCustomerServiceSeed::class)->up();
        \Hyperf\Support\make(PermissionDriveGroupSeed::class)->up();
        \Hyperf\Support\make(PermissionImageGroupSeed::class)->up();
        \Hyperf\Support\make(PermissionImageSeed::class)->up();
        \Hyperf\Support\make(PermissionMemberLevelSeed::class)->up();
        \Hyperf\Support\make(PermissionMemberSeed::class)->up();
        \Hyperf\Support\make(PermissionNavigationSeed::class)->up();
        \Hyperf\Support\make(PermissionOrderSeed::class)->up();
        \Hyperf\Support\make(PermissionPaySeed::class)->up();
        \Hyperf\Support\make(PermissionProductSeed::class)->up();
        \Hyperf\Support\make(PermissionProxySeed::class)->up();
        \Hyperf\Support\make(PermissionRedeemSeed::class)->up();
        \Hyperf\Support\make(PermissionReportSeed::class)->up();
        \Hyperf\Support\make(PermissionSeed::class)->up();
        \Hyperf\Support\make(PermissionTagGroupSeed::class)->up();
        \Hyperf\Support\make(PermissionTagSeed::class)->up();
        \Hyperf\Support\make(PermissionUserStepSeed::class)->up();
        \Hyperf\Support\make(RoleSeed::class)->up();
        \Hyperf\Support\make(UserSeed::class)->up();
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return true;
    }
}

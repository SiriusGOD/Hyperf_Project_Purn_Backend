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
class BaseSeed implements BaseInterface
{
    public function up(): void
    {
        (new ImageGroupSeed)->up();
        (new ImportTagSeed)->up();
        (new ImportImageTagSeed)->up();
        (new CoinSeed)->up();
        (new MemberLevelSeed)->up();
        (new NavigationSeed)->up();
        (new PaySeed)->up();
        (new ProductSeed)->up();
        (new PayCorrespondSeed)->up();
        (new DriveGroupSeed)->up();
        (new ActorClassificationSeed)->up();
        (new ActorSeed)->up();
        (new PermissionAnnouncementSeed)->up();
        (new PermissionClassGroupSeed)->up();
        (new PermissionCustomerServiceSeed)->up();
        (new PermissionDriveGroupSeed)->up();
        (new PermissionImageGroupSeed)->up();
        (new PermissionImageSeed)->up();
        (new PermissionMemberLevelSeed)->up();
        (new PermissionMemberSeed)->up();
        (new PermissionNavigationSeed)->up();
        (new PermissionOrderSeed)->up();
        (new PermissionPaySeed)->up();
        (new PermissionProductSeed)->up();
        (new PermissionProxySeed)->up();
        (new PermissionRedeemSeed)->up();
        (new PermissionReportSeed)->up();
        (new PermissionSeed)->up();
        (new PermissionTagGroupSeed)->up();
        (new PermissionTagSeed)->up();
        (new PermissionUserStepSeed)->up();
        (new RoleSeed)->up();
        (new UserSeed)->up();
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return true;
    }
}

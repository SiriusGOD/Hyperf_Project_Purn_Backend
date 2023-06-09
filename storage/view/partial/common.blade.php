<!-- Preloader -->
<div class="preloader flex-column justify-content-center align-items-center">

</div>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
            <a class="nav-link"   href="#" role="button"> {{auth('session')->user()->name  }}</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Messages Dropdown Menu -->
        <li class="nav-item dropdown">
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <div class="dropdown-divider"></div>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <!-- Message Start -->
                    <div class="media">
                        <img src="/dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                Nora Silvester
                                <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm">The subject goes here</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                        </div>
                    </div>
                    <!-- Message End -->
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
            </div>
        </li>
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/admin/user/logout" role="button">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">

            </div>
            <div class="info">
                <a href="/admin/index/dashboard" class="d-block">{{trans('default.leftbox.tittle') ?? '入口網站後台控制'}}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul id="sidebar" class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <li class="nav-item">
                    <a href="#" class="nav-link {{$navigation_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.navigation_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('navigation-index'))
                            <li class="nav-item">
                                <a href="/admin/navigation/index" class="nav-link {{$navigation_active ?? ''}}">
                                    <i class="nav-icon fas fa-map"></i>
                                    <p>
                                        {{trans('default.leftbox.navigation') ?? '導航管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$advertisement_active ?? $announcement_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.advertisements_announcements_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('advertisement-index'))
                            <li class="nav-item">
                                <a href="/admin/advertisement/index" class="nav-link {{$advertisement_active ?? ''}}">
                                    <i class="nav-icon fas fa-ad"></i>
                                    <p>
                                        {{trans('default.leftbox.advertisement') ?? '廣告管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('announcement-index'))
                            <li class="nav-item">
                                <a href="/admin/announcement/index" class="nav-link {{$announcement_active ?? ''}}">
                                    <i class="nav-icon fas fa-bullhorn"></i>
                                    <p>
                                        {{trans('default.leftbox.announcement') ?? '公告管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$member_active ?? $member_level_active ?? $coin_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.member_coin_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('member-index'))
                            <li class="nav-item">
                                <a href="/admin/member/index" class="nav-link {{$member_active ?? ''}}">
                                    <i class="nav-icon fas fa-user-tag"></i>
                                    <p>
                                        {{trans('default.leftbox.member') ?? '會員管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('memberLevel-index'))
                            <li class="nav-item">
                                <a href="/admin/member_level/index" class="nav-link {{$member_level_active ?? ''}}">
                                    <i class="nav-icon fas fa-user-plus"></i>
                                    <p>
                                        {{trans('default.leftbox.memberLevel') ?? '會員等級管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('coin-index'))
                            <li class="nav-item">
                                <a href="/admin/coin/index" class="nav-link {{$coin_active ?? ''}}">
                                    <i class="nav-icon fas fa-coins"></i>
                                    <p>
                                        {{trans('default.leftbox.coin') ?? '點數管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('redeem-index'))
                            <li class="nav-item">
                                <a href="/admin/redeem/index" class="nav-link {{$redeem_active ?? ''}}">
                                    <i class="nav-icon fas fa-money-bill"></i>
                                    <p>
                                        {{trans('default.leftbox.redeem') ?? '優惠卷管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$video_active ?? $image_active ?? $image_group_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.image_group_video_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('video-index'))
                            <li class="nav-item">
                                <a href="/admin/video/index" class="nav-link {{$video_active ?? ''}}">
                                    <i class="nav-icon fas fa-video"></i>
                                    <p>
                                        {{trans('default.leftbox.video') ?? '影片管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('image-index'))
                            <li class="nav-item">
                                <a href="/admin/image/index" class="nav-link {{$image_active ?? ''}}">
                                    <i class="nav-icon fas fa-image"></i>
                                    <p>
                                        {{trans('default.leftbox.image') ?? '圖片管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('image-group-index'))
                            <li class="nav-item">
                                <a href="/admin/image_group/index" class="nav-link {{$image_group_active ?? ''}}">
                                    <i class="nav-icon fas fa-image"></i>
                                    <p>
                                        {{trans('default.leftbox.image_group') ?? '套圖管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$tag_group_active ?? $tag_active ?? $actor_classification_active ?? $actor_active ??''}}">
                        <p>
                            {{ trans('sidebar.tag_actor_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('tagGroup-index'))
                            <li class="nav-item">
                                <a href="/admin/tag_group/index" class="nav-link {{$tag_group_active ?? ''}}">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>
                                        {{trans('default.leftbox.tagGroup') ?? '標籤群組管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('tag-index'))
                            <li class="nav-item">
                                <a href="/admin/tag/index" class="nav-link {{$tag_active ?? ''}}">
                                    <i class="nav-icon fas fa-tag"></i>
                                    <p>
                                        {{trans('default.leftbox.tag') ?? '標籤管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('actorClassification-index'))
                            <li class="nav-item">
                                <a href="/admin/actor_classification/index" class="nav-link {{$actor_classification_active ?? ''}}">
                                    <i class="nav-icon fas fa-user-friends"></i>
                                    <p>
                                        {{trans('default.leftbox.classification') ?? '演員分類管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('actor-index'))
                            <li class="nav-item">
                                <a href="/admin/actor/index" class="nav-link {{$actor_active ?? ''}}">
                                    <i class="nav-icon fas fa-child"></i>
                                    <p>
                                        {{trans('default.leftbox.actor') ?? '演員管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$product_active ?? $order_active ?? $pay_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.product_order_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('product-index'))
                            <li class="nav-item">
                                <a href="/admin/product/index" class="nav-link {{$product_active ?? ''}}">
                                    <i class="nav-icon fas fa-boxes"></i>
                                    <p>
                                        {{trans('default.leftbox.product') ?? '商品管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('order-index'))
                            <li class="nav-item">
                                <a href="/admin/order/index" class="nav-link {{$order_active ?? ''}}">
                                    <i class="nav-icon fas fa-file"></i>
                                    <p>
                                        {{trans('default.leftbox.order') ?? '訂單管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('pay-index'))
                            <li class="nav-item">
                                <a href="/admin/pay/index" class="nav-link {{$pay_active ?? ''}}">
                                    <i class="nav-icon fas fa-credit-card"></i>
                                    <p>
                                        {{trans('default.leftbox.pay') ?? '支付管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$customer_service_active ?? $report_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.customer_service_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('customer-service-index'))
                            <li class="nav-item">
                                <a href="/admin/customer_service/index" class="nav-link {{$customer_service_active ?? ''}}">
                                    <i class="nav-icon fas fa-envelope"></i>
                                    <p>
                                        {{trans('default.leftbox.customer_service') ?? '客服系統'}}
                                    </p>
                                </a>
                            </li>
                        @endif

                        @if(authPermission('report-index'))
                            <li class="nav-item">
                                <a href="/admin/report/index" class="nav-link {{$report_active ?? ''}}">
                                    <i class="nav-icon fas fa-bug"></i>
                                    <p>
                                        {{trans('default.leftbox.report') ?? '檢舉'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$channel_active ?? $proxy_active ?? $withdraw_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.channel_proxy_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('channel-index'))
                            <li class="nav-item">
                                <a href="/admin/channel/index" class="nav-link {{$channel_active ?? ''}}">
                                    <i class="nav-icon fas fa-sitemap"></i>
                                    <p>
                                        {{trans('default.leftbox.channel') ?? '渠道管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif

                        @if(authPermission('proxy-index'))
                            <li class="nav-item">
                                <a href="/admin/proxy/index" class="nav-link {{$proxy_active ?? ''}}">
                                    <i class="nav-icon far fa-id-card"></i>
                                    <p>
                                        {{trans('default.leftbox.proxy') ?? '代理管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif

                        @if(authPermission('withdraw-index'))
                            <li class="nav-item">
                                <a href="/admin/withdraw/index" class="nav-link {{$withdraw_active ?? ''}}">
                                    <i class="nav-icon fas fa-wallet"></i>
                                    <p>
                                        {{trans('default.leftbox.withdraw') ?? '提現管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$drive_class_active ?? $drive_group_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.drive_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('driveClass-index'))
                            <li class="nav-item">
                                <a href="/admin/drive_class/index" class="nav-link {{$drive_class_active ?? ''}}">
                                    <i class="nav-icon fab fa-telegram"></i>
                                    <p>
                                        {{trans('default.leftbox.driveClass') ?? '車群類別管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('driveGroup-index'))
                            <li class="nav-item">
                                <a href="/admin/drive_group/index" class="nav-link {{$drive_group_active ?? ''}}">
                                    <i class="nav-icon fab fa-twitter"></i>
                                    <p>
                                        {{trans('default.leftbox.driveGroup') ?? '車群管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{$user_active ?? $role_active ?? $user_step_active ?? $activity_active ?? ''}}">
                        <p>
                            {{ trans('sidebar.backend_manager') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(authPermission('manager-index'))
                            <li class="nav-item">
                                <a href="/admin/manager/index" class="nav-link {{$user_active ?? ''}}">
                                    <i class="nav-icon far fa-user"></i>
                                    <p>
                                        {{trans('default.leftbox.manager') ?? '使用者管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('role-index'))
                            <li class="nav-item">
                                <a href="/admin/role/index" class="nav-link {{$role_active ?? ''}}">
                                    <i class="nav-icon fas fa-user-tag"></i>
                                    <p>
                                        {{trans('default.leftbox.role') ?? '角色管理'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('user-step-index'))
                            <li class="nav-item">
                                <a href="/admin/user_step/index" class="nav-link {{$user_step_active ?? ''}}">
                                    <i class="nav-icon fas fa-chalkboard"></i>
                                    <p>
                                        {{trans('default.leftbox.user-step') ?? '操作日誌'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if(authPermission('user-step-index'))
                            <li class="nav-item">
                                <a href="/admin/activity/index" class="nav-link {{$activity_active ?? ''}}">
                                    <i class="nav-icon fas fa-chalkboard"></i>
                                    <p>
                                        {{trans('default.leftbox.activity') ?? '用戶日誌'}}
                                    </p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

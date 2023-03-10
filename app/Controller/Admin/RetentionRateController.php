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
namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Middleware\PermissionMiddleware;
use App\Model\VisitorActivity;
use App\Traits\SitePermissionTrait;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use HyperfExt\Jwt\Contracts\JwtFactoryInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;
use HyperfExt\Jwt\Jwt;

/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class RetentionRateController extends AbstractController
{
    use SitePermissionTrait;

    // 預設時間
    public const DEFAULT_DAY = 7;

    public const RETENTION_RATE_DAYS = [
        'next' => 1,
        'three' => 3,
        'seven' => 7,
    ];

    /**
     * 提供了对 JWT 编解码、刷新和失活的能力。
     */
    protected ManagerInterface $manager;

    /**
     * 提供了从请求解析 JWT 及对 JWT 进行一系列相关操作的能力。
     */
    protected Jwt $jwt;

    protected RenderInterface $render;

    /**
     * @Inject
     */
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(ManagerInterface $manager, JwtFactoryInterface $jwtFactory, RenderInterface $render)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->jwt = $jwtFactory->make();
        $this->render = $render;
    }

    /**
     * @RequestMapping(path="list", methods={"GET"})
     */
    public function list(RequestInterface $request)
    {
        // 顯示幾筆
        $startTime = $request->input('base_time', Carbon::today()->subDays(self::DEFAULT_DAY)->toDateString());
        $siteId = $request->input('site_id');

        $query = VisitorActivity::where('visit_date', $startTime);
        $query = $this->attachQueryBuilder($query);

        if (! empty($siteId)) {
            $query->where('site_id', $siteId);
        }

        $baseModels = $query->get(Db::raw('DISTINCT ip'));
        $baseTotal = $baseModels->count();

        $ips = [];
        foreach ($baseModels as $model) {
            $ips[] = $model->ip;
        }

        $baseDate = Carbon::parse($startTime . ' 00:00:00');
        $result = [];
        $result['base_total'] = $baseTotal;
        foreach (self::RETENTION_RATE_DAYS as $key => $day) {
            $query = VisitorActivity::whereBetween('visit_date', [$baseDate->copy()->addDay()->toDateString(), $baseDate->copy()->addDays($day)->toDateString()])
                ->whereIn('ip', $ips);

            $query = $this->attachQueryBuilder($query);

            if (! empty($siteId)) {
                $query->where('site_id', $siteId);
            }
            $result[$key . '_total'] = $query->count(Db::raw('DISTINCT ip'));
            $result[$key . '_rate'] = 0;
            if ($result[$key . '_total'] != 0) {
                $result[$key . '_rate'] = ceil(($result[$key . '_total'] / $result['base_total']) * 100);
            }
        }

        $data['data'] = $result;
        $data['base_time'] = $startTime;
        $data['site_id'] = $siteId;
        $data['navbar'] = trans('default.retentionrate_control.retentionrate_control');
        $data['retention_rate_active'] = 'active';
        return $this->render->render('admin.retentionRate.list', $data);
    }
}

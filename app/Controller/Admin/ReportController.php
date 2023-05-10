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
use App\Model\ImageGroup;
use App\Model\Report;
use App\Model\Video;
use App\Service\ReportService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class ReportController extends AbstractController
{
    protected RenderInterface $render;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }

    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request, ReportService $service)
    {
        // 顯示幾筆
        $step = Report::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = Report::with('member')
            ->where('type', Report::TYPE['report'])
            ->where('status', Report::STATUS['new'])
            ->orderByDesc('id')
            ->offset(($page - 1) * $step)->limit($step)->get();

        $models = $service->generateReport($models->toArray());

        $total = Report::where('type', Report::TYPE['report'])->where('status', Report::STATUS['new'])->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.report_control.report_control');
        $data['report_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/reprot/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.report.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'status')]
    public function status(RequestInterface $request, ResponseInterface $response)
    {
        $id = $request->input('id');
        $status = $request->input('status');

        $report = Report::find($id);

        if ($status == Report::STATUS['pass']) {
            match ($report->model_type) {
                ImageGroup::class => ImageGroup::where('id', $report->model_id)->delete(),
                Video::class => Video::where('id', $report->model_id)->delete()
            };
        }

        Report::where('model_type', $report->model_type)
            ->where('model_id', $report->model_id)
            ->update([
                'status' => $status,
            ]);

        return $response->redirect('/admin/report/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'image')]
    public function image(RequestInterface $request, ResponseInterface $response)
    {
        $id = $request->input('id');
        $model = ImageGroup::withTrashed()
            ->where('id', $id)
            ->with('images')
            ->first()
            ->toArray();

        return $this->render->render('admin.report.image', [
            'model' => $model,
        ]);
    }
}

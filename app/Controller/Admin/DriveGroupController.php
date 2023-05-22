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
use App\Model\DriveGroup;
use App\Model\DriveGroupHasClass;
use Hyperf\DbConnection\Db;
use App\Service\DriveGroupService;
use App\Util\General;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class DriveGroupController extends AbstractController
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
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        // 顯示幾筆
        $step = DriveGroup::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = DriveGroup::with('user')->leftjoin('drive_group_has_class', 'drive_groups.id', 'drive_group_has_class.drive_group_id')
                ->leftjoin('drive_class', 'drive_class.id', 'drive_group_has_class.drive_class_id')
                ->select('drive_groups.*', Db::raw("GROUP_CONCAT(drive_class.name SEPARATOR ' , ') as class_name "))
                ->groupBy('drive_groups.id')
                ->whereNull('drive_groups.deleted_at')->offset(($page - 1) * $step)->limit($step)->get();
        $total = DriveGroup::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.drive_group_control.drive_group_control');
        $data['drive_group_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/drive_group/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.driveGroup.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.drive_group_control.drive_group_insert');
        $data['drive_group_active'] = 'active';
        $data['drive_class_ids'] = '';
        return $this->render->render('admin.driveGroup.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(RequestInterface $request, ResponseInterface $response, DriveGroupService $service): PsrResponseInterface
    {
        $data['user_id'] = auth('session')->user()->getId();
        $data['id'] = $request->input('id');
        $data['name'] = $request->input('name');
        $data['groups'] = $request->input('groups', []);
        $data['url'] = $request->input('url');
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $dataArr = General::uploadImage($file, 'icons');
            $imageUrl = $dataArr['url'];
            // $data['height'] = $dataArr['height'];
            // $data['weight'] = $dataArr['weight'];
        }
        if (! empty($imageUrl)) {
            $data['image_url'] = $imageUrl;
        }
        $service->storeDriveGroup($data);
        return $response->redirect('/admin/drive_group/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = DriveGroup::findOrFail($id);
        $data['navbar'] = trans('default.drive_class_control.drive_class_edit');
        $data['drive_group_active'] = 'active';
        $data['drive_class_ids'] = DriveGroupHasClass::where('drive_group_id', $id)->get()->pluck('drive_class_id');
        return $this->render->render('admin.driveGroup.form', $data);
    }
}

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
use App\Model\Advertisement;
use App\Request\AdvertisementRequest;
use App\Service\AdvertisementService;
use Carbon\Carbon;
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
class AdvertisementController extends AbstractController
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
    public function index(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Advertisement::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Advertisement::offset(($page - 1) * $step)->limit($step);
        $advertisements = $query->get();
        $query = Advertisement::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.ad_control.ad_control');
        $data['advertisement_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $advertisements;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/advertisement/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($advertisements, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.advertisement.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(AdvertisementRequest $request, ResponseInterface $response, AdvertisementService $service): PsrResponseInterface
    {
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $request->file('image')->getExtension();
            $filename = sha1(Carbon::now()->toDateTimeString());
            if (! file_exists(BASE_PATH . '/public/advertisement')) {
                mkdir(BASE_PATH . '/public/advertisement', 0755);
            }
            $imageUrl = '/advertisement/' . $filename . '.' . $extension;
            $file->moveTo(BASE_PATH . '/public' . $imageUrl);
        }
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = auth('session')->user()->id;
        $data['name'] = $request->input('name');
        if (! empty($imageUrl)) {
            $data['image_url'] = $imageUrl;
        }
        $data['url'] = $request->input('url');
        $data['position'] = $request->input('position');
        $data['start_time'] = $request->input('start_time');
        $data['end_time'] = $request->input('end_time');
        $data['buyer'] = $request->input('buyer');
        $data['expire'] = $request->input('expire');
        $service->storeAdvertisement($data);
        return $response->redirect('/admin/advertisement/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.ad_control.ad_insert');
        $data['advertisement_active'] = 'active';
        $model = new Advertisement();
        $model->expire = Advertisement::EXPIRE['no'];
        $model->position = Advertisement::POSITION['top_banner'];
        $data['model'] = $model;
        return $this->render->render('admin.advertisement.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Advertisement::findOrFail($id);
        $data['navbar'] = trans('default.ad_control.ad_update');
        $data['advertisement_active'] = 'active';
        return $this->render->render('admin.advertisement.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'expire')]
    public function expire(RequestInterface $request, ResponseInterface $response, AdvertisementService $service): PsrResponseInterface
    {
        $query = Advertisement::where('id', $request->input('id'));
        $record = $query->first();
        if (empty($record)) {
            return $response->redirect('/admin/advertisement/index');
        }
        $record->expire = $request->input('expire', 1);
        $record->save();
        $service->updateCache();
        return $response->redirect('/admin/advertisement/index');
    }
}

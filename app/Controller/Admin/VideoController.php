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

use App\Middleware\PermissionMiddleware;
use App\Controller\AbstractController;
use App\Model\Video;
use App\Request\VideoRequest;
use App\Service\VideoService;
//use App\Traits\SitePermissionTrait;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFvideoyInterface;
use Hyperf\View\RenderInterface;
use HyperfExt\Jwt\Contracts\JwtFvideoyInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;
use HyperfExt\Jwt\Jwt;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class VideoController extends AbstractController
{
    //use SitePermissionTrait;

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
    protected ValidatorFvideoyInterface $validationFvideoy;

    public function __construct(ManagerInterface $manager, JwtFvideoyInterface $jwtFvideoy, RenderInterface $render)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->jwt = $jwtFvideoy->make();
        $this->render = $render;
    }

    /**
     * @RequestMapping(path="index", methods={"GET"})
     */
    public function index(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Video::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Video::offset(($page - 1) * $step)
            ->limit($step);
        $videos = $query->get();
        $query = Video::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.video.title');
        $data['video_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $videos;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/video/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($videos, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.video.index', $data);
    }

    /**
     * @RequestMapping(path="store", methods={"POST"})
     */
    public function store(VideoRequest $request, ResponseInterface $response, VideoService $service): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = auth('session')->user()->id;
        $data['name'] = $request->input('name');
        $data['sex'] = $request->input('sex');
        $service->storeVideo($data);
        return $response->redirect('/admin/video/index');
    }

    /**
     * @RequestMapping(path="create", methods={"get"})
     */
    public function create()
    {
        $data['navbar'] = trans('default.video.insert');
        $data['video_active'] = 'active';
        $model = new Video();
        $data['model'] = $model;
        return $this->render->render('admin.video.form', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Video::findOrFail($id);
        $data['navbar'] = trans('default.ad_control.ad_update');
        $data['video_active'] = 'active';
        return $this->render->render('admin.video.form', $data);
    }

    /**
     * @RequestMapping(path="expire", methods={"POST"})
     */
    public function expire(RequestInterface $request, ResponseInterface $response, VideoService $service): PsrResponseInterface
    {
        $query = Video::where('id', $request->input('id'));
        $record = $query->first();

        if (empty($record)) {
            return $response->redirect('/admin/video/index');
        }

        $record->expire = $request->input('expire', 1);
        $record->save();
        $service->updateCache();
        return $response->redirect('/admin/video/index');
    }
}


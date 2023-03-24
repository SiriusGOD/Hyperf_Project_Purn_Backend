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

use App\Constants\VideoCode;
use App\Controller\AbstractController;
use App\Middleware\PermissionMiddleware;
use App\Model\Video;
use App\Request\VideoRequest;
use App\Service\ActorService;
use App\Service\TagService;
use App\Service\VideoService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use HyperfExt\Jwt\Contracts\JwtFactoryInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;
use HyperfExt\Jwt\Jwt;

/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class VideoController extends AbstractController
{
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
     * @RequestMapping(path="index", methods={"GET"})
     */
    public function index(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Video::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Video::offset(($page - 1) * $step)->limit($step);
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
        $data['const'] = VideoCode::class;
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
    public function store(VideoRequest $request, ResponseInterface $response, VideoService $videoService, TagService $tagService, ActorService $actorService)
    {
        $data = $request->all();
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = auth('session')->user()->id;
        $video = $videoService->storeVideo($data);
        $tagService->videoCorrespondTag($data, $video->id);
        $actorService->videoCorrespondActor($data, $video->id);
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
        $data['video'] = $model;
        $data['const'] = VideoCode::class;
        return $this->render->render('admin.video.form', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['video'] = Video::findOrFail($id);
        $data['navbar'] = trans('default.video.update_video');
        $data['video_active'] = 'active';
        $data['const'] = VideoCode::class;
        return $this->render->render('admin.video.form', $data);
    }
}

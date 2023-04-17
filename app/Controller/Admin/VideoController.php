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
use App\Model\Image;
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

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class VideoController extends AbstractController
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
    public function index(RequestInterface $request, VideoService $service)
    {
        // 顯示幾筆
        $step = Video::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = $service->adminSearchVideoQuery([
            'page' => $page,
            'status' => $request->input('status'),
            'title' => $request->input('title'),
            'start_duration' => $request->input('start_duration'),
            'end_duration' => $request->input('end_duration'),
            'tag_ids' => $request->input('tags'),
        ]);

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
        $data['title'] = $request->input('title');
        $data['status'] = $request->input('status', '');
        $data['start_duration'] = $request->input('start_duration');
        $data['end_duration'] = $request->input('end_duration');
        $data['tag_ids'] = json_encode(TagService::tagIdsToInt($request->input('tags')));
        $paginator = new Paginator($videos, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.video.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
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

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.video.insert');
        $data['video_active'] = 'active';
        $model = new Video();
        $data['video'] = $model;
        $data['const'] = VideoCode::class;
        return $this->render->render('admin.video.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
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

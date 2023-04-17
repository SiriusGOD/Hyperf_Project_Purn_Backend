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
use App\Model\TagCorrespond;
use App\Request\ImageRequest;
use App\Service\ImageGroupService;
use App\Service\ImageService;
use App\Service\TagService;
use Hyperf\DbConnection\Db;
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
class ImageGroupController extends AbstractController
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
        $step = ImageGroup::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = ImageGroup::with(['user'])->offset(($page - 1) * $step)
            ->limit($step)
            ->leftJoin('clicks', function($join) {
                $join->on('image_groups.id', '=', 'clicks.type_id')->where('clicks.type', ImageGroup::class);
            })
            ->leftJoin('likes', function($join) {
                $join->on('image_groups.id', '=', 'likes.type_id')->where('likes.type', ImageGroup::class);
            })
            ->select('image_groups.*', Db::raw('clicks.count as click_count'), Db::raw('likes.count as like_count'));
        $models = $query->get();
        $query = ImageGroup::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.image_group_control.image_group_control');
        $data['image_group_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/image_group/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.imageGroup.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(
        ImageRequest $request,
        ResponseInterface $response,
        ImageService $service,
        TagService $tagService,
        ImageGroupService $groupService
    ): PsrResponseInterface {
        $imageUrl = null;
        $thumbnail = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $result = $service->moveImageFile($file);
            $thumbnail = $service->createThumbnail($result['path']);
            $imageUrl = $result['url'];
        }
        $data = [];
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = auth('session')->user()->id;
        $data['title'] = $request->input('title');
        if (! empty($imageUrl)) {
            $data['url'] = $imageUrl;
            $data['thumbnail'] = $thumbnail;
        }
        $data['description'] = $request->input('description', '');
        $image = $groupService->storeImageGroup($data);
        $tagService->createTagRelationshipArr(ImageGroup::class, $image->id, $request->input('tags'));
        return $response->redirect('/admin/image_group/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.image_control.image_insert');
        $data['image_active'] = 'active';
        return $this->render->render('admin.imageGroup.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = ImageGroup::findOrFail($id);
        $data['navbar'] = trans('default.image_group_control.image_group_update');
        $data['image_group_active'] = 'active';
        $data['tag_ids'] = TagCorrespond::where('correspond_type', ImageGroup::class)->where('correspond_id', $id)->get()->pluck('tag_id');
        return $this->render->render('admin.imageGroup.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'delete')]
    public function delete(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        ImageGroup::where('id', $request->input('id'))->delete();
        return $response->redirect('/admin/image_group/index');
    }
}

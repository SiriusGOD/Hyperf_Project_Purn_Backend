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
use App\Model\Advertisement;
use App\Model\Image;
use App\Model\TagCorrespond;
use App\Request\AdvertisementRequest;
use App\Request\ImageRequest;
use App\Service\AdvertisementService;
use App\Service\ImageService;
use App\Service\TagService;
use App\Traits\SitePermissionTrait;
use Carbon\Carbon;
use Hyperf\Database\Model\Relations\MorphTo;
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
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class ImageController extends AbstractController
{
    use SitePermissionTrait;

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
        $step = Image::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Image::with([
            'user',
        ])
            ->offset(($page - 1) * $step)
            ->limit($step);
        $models = $query->get();

        $query = Image::select('*');
        $total = $query->count();

        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.ad_control.ad_control');
        $data['image_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/image/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);

        $data['paginator'] = $paginator->toArray();

        return $this->render->render('admin.image.index', $data);
    }

    /**
     * @RequestMapping(path="store", methods={"POST"})
     */
    public function store(ImageRequest $request, ResponseInterface $response, ImageService $service, TagService $tagService): PsrResponseInterface
    {
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
        $data['group_id'] = $request->input('group_id', 0);
        $data['description'] = $request->input('description');
        $image = $service->storeImage($data);
        $tagService->createTagRelationshipArr(Image::class, $image->id, $request->input('tags'));
        return $response->redirect('/admin/image/index');
    }

    /**
     * @RequestMapping(path="create", methods={"get"})
     */
    public function create()
    {
        $data['navbar'] = trans('default.image_control.image_insert');
        $data['image_active'] = 'active';
        return $this->render->render('admin.image.form', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Image::findOrFail($id);
        $data['navbar'] = trans('default.image_control.image_update');
        $data['image_active'] = 'active';
        $data['tag_ids'] = TagCorrespond::where('correspond_type', Image::class)
            ->where('correspond_id', $id)
            ->get()
            ->pluck('tag_id');
        return $this->render->render('admin.image.form', $data);
    }

    /**
     * @RequestMapping(path="delete", methods={"get"})
     */
    public function delete(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        $query = Image::where('id', $request->input('id'));
        $record = $query->first();

        if (empty($record)) {
            return $response->redirect('/admin/image/index');
        }
        $record->delete();
        return $response->redirect('/admin/image/index');
    }
}

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
use App\Model\Tag;
use App\Model\TagHasGroup;
use App\Request\TagRequest;
use App\Service\TagService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use App\Util\General;
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
class TagController extends AbstractController
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
        $tag_name = $request->input('tag_name');
        // 顯示幾筆
        $step = Tag::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = Tag::with('user')->leftjoin('tag_has_groups', 'tags.id', 'tag_has_groups.tag_id')
            ->leftjoin('tag_groups', 'tag_has_groups.tag_group_id', 'tag_groups.id')
            ->select('tags.*', Db::raw("GROUP_CONCAT(tag_groups.name SEPARATOR ' , ') as group_name "))
            ->groupBy('tags.id');
        if($tag_name){
          $models = $models->where('tags.name' ,'like' , "%{$tag_name}%");
        }
        $models = $models->offset(($page - 1) * $step)->limit($step)->get();
        $total = Tag::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.tag_control.tag_control');
        $data['tag_active'] = 'active';
        $data['tag_name'] = isset($tag_name) ? $tag_name : "";
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/tag/index';
        $data['next'] = $path . '?page=' . ($page + 1)."&tag_name=".$tag_name;
        $data['prev'] = $path . '?page=' . ($page - 1)."&tag_name=".$tag_name;
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.tag.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(TagRequest $request, ResponseInterface $response, TagService $service): PsrResponseInterface
    {
        $data['user_id'] = auth('session')->user()->getId();
        $data['id'] = $request->input('id');
        $data['name'] = $request->input('name');
        $data['groups'] = $request->input('groups', []);
        $data['hot_order'] = $request->input('hot_order');
        $data['is_init'] = empty($request->input('is_init')) ? 0 : 1;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $dataArr = General::uploadImage($file, 'tag');
            $imageUrl = $dataArr['url'];
            // $data['height'] = $dataArr['height'];
            // $data['weight'] = $dataArr['weight'];
        }
        if (! empty($imageUrl)) {
            $data['image_url'] = $imageUrl;
        }
        $service->createTag($data);
        return $response->redirect('/admin/tag/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.tag_control.tag_insert');
        $data['tag_active'] = 'active';
        $data['tag_group_ids'] = '';
        return $this->render->render('admin.tag.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['navbar'] = trans('default.tag_control.tag_edit');
        $data['tag_active'] = 'active';
        $data['tag_group_ids'] = TagHasGroup::where('tag_id', $id)->get()->pluck('tag_group_id');
        $data['model'] = Tag::find($id);
        return $this->render->render('admin.tag.form', $data);
    }
}

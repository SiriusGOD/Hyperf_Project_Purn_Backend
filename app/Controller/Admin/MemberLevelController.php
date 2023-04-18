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
use App\Model\MemberLevel;
use App\Request\MemberLevelStoreRequest;
use App\Service\MemberLevelService;
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
class MemberLevelController extends AbstractController
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
        $step = MemberLevel::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = MemberLevel::offset(($page - 1) * $step)->limit($step);
        $member_levels = $query->get();
        $query = MemberLevel::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.member_level_control.member_level_control');
        $data['memberLevel_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $member_levels;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/order/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($member_levels, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.memberLevel.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.member_level_control.member_level_insert');
        $data['memberLevel_active'] = 'active';
        return $this->render->render('admin.memberLevel.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(MemberLevelStoreRequest $request, ResponseInterface $response, MemberLevelService $service): PsrResponseInterface
    {
        $userId = auth('session')->user()->getId();
        $id = $request->input('id', 0);
        $type = $request->input('type');
        $name = $request->input('name');
        $duration = $request->input('duration');
        $service->store([
            'id' => $id,
            'user_id' => $userId,
            'type' => $type,
            'name' => $name,
            'duration' => $duration,
        ]);
        return $response->redirect('/admin/member_level/index');
    }
}

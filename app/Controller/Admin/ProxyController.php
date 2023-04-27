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

use App\Constants\Constants;
use App\Controller\AbstractController;
use App\Service\MemberService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\View\RenderInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class ProxyController extends AbstractController
{
    protected RenderInterface $render;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }

    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request, MemberService $memberService)
    {
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $users = $memberService->getList($page, Constants::DEFAULT_PAGE_PER);
        $total = $memberService->allCount();
        $data['last_page'] = ceil($total / Constants::DEFAULT_PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['total'] = $total;
        $data['datas'] = $users;
        $data['page'] = $page;
        $data['step'] = 10;
        $path = '/admin/proxy/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.proxy.title');
        $data['proxy_active'] = 'active';
        return $this->render->render('admin.proxy.index', $data);
    }
}

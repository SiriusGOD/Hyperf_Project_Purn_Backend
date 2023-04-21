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
use App\Model\Coin;
use App\Request\CoinStoreRequest;
use App\Service\CoinService;
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
class CoinController extends AbstractController
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
        $step = Coin::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Coin::offset(($page - 1) * $step)->limit($step);
        $member_levels = $query->get();
        $query = Coin::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.coin_control.coin_control');
        $data['coin_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $member_levels;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/coin/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($member_levels, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.coin.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.coin_control.coin_insert');
        $data['coin_active'] = 'active';
        return $this->render->render('admin.coin.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(CoinStoreRequest $request, ResponseInterface $response, CoinService $service): PsrResponseInterface
    {
        $userId = auth('session')->user()->getId();
        $id = $request->input('id', 0);
        $type = $request->input('type');
        $name = $request->input('name');
        $points = $request->input('points');
        $service->store([
            'id' => $id,
            'user_id' => $userId,
            'type' => $type,
            'name' => $name,
            'points' => $points,
        ]);
        return $response->redirect('/admin/coin/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Coin::findOrFail($id);
        $data['navbar'] = trans('default.coin_control.coin_edit');
        $data['coin_active'] = 'active';
        return $this->render->render('admin.coin.form', $data);
    }
}

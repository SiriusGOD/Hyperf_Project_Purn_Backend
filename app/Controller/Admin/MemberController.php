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
use App\Model\Member;
use App\Model\MemberLevel;
use App\Model\User;
use App\Request\MemberUpdateRequest;
use App\Service\MemberService;
use App\Service\RoleService;
use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class MemberController extends AbstractController
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
    public function index(RequestInterface $request, MemberService $service, RoleService $roleService)
    {
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $users = $service->getList($page, User::PAGE_PER);
        $total = $service->allCount();
        $data['last_page'] = ceil($total / User::PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['roles'] = $roleService->getAll()->toArray();
        $data['total'] = $total;
        $data['datas'] = $users;
        $data['page'] = $page;
        $data['step'] = User::PAGE_PER;
        $path = '/admin/member/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.member_control.member_control');
        $data['member_active'] = 'active';
        return $this->render->render('admin.member.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(MemberUpdateRequest $request, ResponseInterface $response, MemberService $service): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $path = '';
        if ($request->hasFile('avatar')) {
            $path = $service->moveUserAvatar($request->file('avatar'));
        }
        $data['avatar'] = $path;
        $data['name'] = $request->input('name');
        $data['account'] = $request->input('account');
        $data['sex'] = $request->input('sex');
        $data['age'] = $request->input('age');
        $data['email'] = $request->input('email');
        $data['phone'] = $request->input('phone');
        $data['status'] = $request->input('status');
        $data['role_id'] = $request->input('role_id');
        $data['password'] = $request->input('password');
        $data['member_level_status'] = $request->input('member_level_status', 0);
        $data['start_time'] = $request->input('start_time');
        $data['end_time'] = $request->input('end_time');
        $data['coins'] = $request->input('coins', 0);
        $data['diamond_coins'] = $request->input('diamond_coins', 0);
        $data['free_quota'] = $request->input('free_quota', 1);
        // $data['free_quota_limit'] = $request->input('free_quota_limit', 1);
        $data['diamond_quota'] = empty($request->input('diamond_quota')) ? 0 : $request->input('diamond_quota');
        $data['vip_quota'] = empty($request->input('vip_quota')) ? 0 : $request->input('vip_quota');
        
        // 修改會員等級時 如果要改非體驗會員則會員等級須設定大於１天
        $start_time = Carbon::parse($data['start_time']);
        $end_time = Carbon::parse($data['end_time']);
        $diff_days = $end_time->diffInDays($start_time);
        if($data['member_level_status'] == MemberLevel::TYPE_VALUE['vip'] && $diff_days > 1){
            $data['diamond_quota'] = empty($request->input('diamond_quota')) ? 0 : $request->input('diamond_quota');
            if(empty($request->input('vip_quota'))){
                $data['vip_quota'] = Member::VIP_QUOTA['UP_TWO'];
            }else{
                $data['vip_quota'] = $request->input('vip_quota');
            }  
        }
        if($data['member_level_status'] == MemberLevel::TYPE_VALUE['diamond'] && $diff_days > 1){
            $data['vip_quota'] = empty($request->input('vip_quota')) ? 0 : $request->input('vip_quota');
            if(empty($request->input('diamond_quota'))){
                $data['diamond_quota'] = Member::VIP_QUOTA['UP_TWO'];
            }else{
                $data['diamond_quota'] = $request->input('diamond_quota');
            }
        }

        // 如果是設定體驗卡
        if($data['member_level_status'] == MemberLevel::TYPE_VALUE['vip'] && $diff_days <= 1){
            if(empty($data['vip_quota']))$data['vip_quota'] = Member::VIP_QUOTA['DAY'];
        }
        if($data['member_level_status'] == MemberLevel::TYPE_VALUE['diamond'] && $diff_days <= 1){
            if(empty($data['diamond_quota']))$data['diamond_quota'] = Member::VIP_QUOTA['DAY'];
        }

        // 依照會員狀態設定免費觀看次數
        $data['free_quota_limit'] = $data['status'] + 1;
        if($data['free_quota'] > $data['free_quota_limit']){
            $data['free_quota'] = $data['free_quota_limit'];
        }

        // 會員等級為０時
        if($data['member_level_status'] == MemberLevel::NO_MEMBER_LEVEL){
            $data['vip_quota'] = 0;
            $data['diamond_quota'] = 0;
        }

        $service->storeUser($data);
        return $response->redirect('/admin/member/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create(RoleService $roleService)
    {
        $data['google2fa_url'] = '';
        $data['qrcode_image'] = '';
        $data['navbar'] = trans('default.member_control.member_insert');
        $data['user'] = new Member();
        $data['roles'] = $roleService->getAll();
        $data['member_active'] = 'active';
        return $this->render->render('admin.member.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request, MemberService $service, RoleService $roleService)
    {
        $id = $request->input('id');
        $user = Member::leftJoin('buy_member_levels', function ($join) {
            $join->on('members.id', '=', 'buy_member_levels.member_id')
                ->orWhereNull('buy_member_levels.member_id')
                ->whereNull('buy_member_levels.deleted_at');
        })
            ->select('members.*', 'buy_member_levels.start_time', 'buy_member_levels.end_time')
            ->where('members.id', $id)
            ->first();

        $data['user'] = $user;
        $data['navbar'] = trans('default.member_control.member_update');
        $data['member_active'] = 'active';
        $data['roles'] = $roleService->getAll();
        return $this->render->render('admin.member.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, ResponseInterface $response, MemberService $service): PsrResponseInterface
    {
        $service->deleteUser($request->input('id'));
        return $response->redirect('/admin/member/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'search')]
    public function search(RequestInterface $request, MemberService $service, RoleService $roleService)
    {
        // 顯示幾筆
        $step = Member::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $account = $request->input('account') ?? '';
        $users = $service->adminAccountSearch($account, $page, $step);
        $total = $service->adminAccountSearchCount($account);
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['roles'] = $roleService->getAll()->toArray();
        $data['total'] = $total;
        $data['datas'] = $users;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/member/search';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.member_control.member_control');
        $data['member_active'] = 'active';
        return $this->render->render('admin.member.index', $data);
    }
}

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

use App\Constants\RedeemCode;
use App\Controller\AbstractController;
use App\Model\Channel;
use App\Service\ChannelService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\View\RenderInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class ChannelController extends AbstractController
{
    protected RenderInterface $render;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }
    //列表頁
    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request, ChannelService $channelService)
    {
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $res = $channelService->getChannels($page , Channel::PAGE_PER);
        $total = $channelService->thisCont();
        $data['last_page'] = ceil($total / Channel::PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['total'] = $total;
        $data['datas'] = $res;
        $data['page'] = $page;
        $data['step'] = 10;
        $data['category'] = RedeemCode::CATEGORY;
        $path = '/admin/channel/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.channels.title');
        $data['channel_active'] = 'active';
        return $this->render->render('admin.channel.index', $data);
    }

    //渠道詳細資料
    #[RequestMapping(methods: ['GET'], path: 'detail')]
    public function detail(RequestInterface $request, ChannelService $channelService)
    {
        $duration = $request->input('duration');
        $id = $request->input('id');
        $data['model'] = $channelService->getChannel((int) $id);
        $data['calcs'] =(!empty($duration)) ?  $channelService->calcTotal($duration) : null;
        $data['navbar'] = trans('default.channels.detail');
        $data['channel_active'] = 'active';
        $data['calc'] = 'active';
        return $this->render->render('admin.channel.form', $data);
    }

}

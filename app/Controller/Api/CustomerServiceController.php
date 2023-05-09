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
namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\CustomerService;
use App\Model\CustomerServiceDetail;
use App\Request\CustomerServiceApiReplyRequest;
use App\Request\CustomerServiceCreateRequest;
use App\Request\CustomerServiceDetailRequest;
use App\Service\CustomerServiceService;
use App\Service\ImageService;
use App\Util\General;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Qbhy\HyperfAuth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class CustomerServiceController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request)
    {
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', CustomerService::PAGE_PER);
        $memberId = auth()->user()->getId();
        $models = CustomerService::where('member_id', $memberId)
            ->offset($page * $limit)
            ->limit($limit)
            ->orderByDesc('id')
            ->get()
            ->toArray();
        $data = [];
        $data['models'] = $models;
        $path = '/api/customer_service/list';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'detail')]
    public function detail(RequestInterface $request)
    {
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', CustomerService::PAGE_PER);
        $id = $request->input('id');
        $models = CustomerServiceDetail::where('customer_service_id', $id)
            ->with('user', 'member')
            ->offset($page * $limit)
            ->limit($limit)
            ->get()
            ->toArray();


        $result = [];
        $url = General::getImageUrl($request->url());
        foreach ($models as $model) {
            $model['image_url'] = $url . $model['image_url'];
            $result[] = $model;
        }
        $data = [];
        $data['models'] = $result;
        $path = '/api/customer_service/detail';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(RequestInterface $request, CustomerServiceService $service)
    {
        $memberId = auth()->user()->getId();
        $model = $service->create([
            'member_id' => $memberId,
            'type' => $request->input('type'),
            'title' => $request->input('title'),
        ]);
        return $this->success(['id' => $model->id]);
    }

    #[RequestMapping(methods: ['POST'], path: 'reply')]
    public function reply(RequestInterface $request, CustomerServiceService $service, ImageService $imageService): PsrResponseInterface
    {
        $userId = auth()->user()->getId();
        $id = (int) $request->input('id');
        $message = $request->input('message');
        $params = [
            'id' => $id,
            'member_id' => $userId,
            'message' => $message,
        ];
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $result = $imageService->moveImageFile($file);
            $params['image_url'] = $result['url'];
        }
        $service->reply($params);
        $service->setApiDetailRead($id);
        return $this->success([]);
    }
}

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
use App\Model\CustomerService;
use App\Model\CustomerServiceDetail;
use App\Request\CustomerServiceApiReplyRequest;
use App\Request\CustomerServiceCreateRequest;
use App\Request\CustomerServiceDetailRequest;
use App\Service\CustomerServiceService;
use App\Service\ImageService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Qbhy\HyperfAuth\AuthMiddleware;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class CustomerServiceController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request)
    {
        $page = (int) $request->input('page', 0);
        $memberId = auth()->user()->getId();
        $models = CustomerService::where('member_id', $memberId)
            ->offset($page * CustomerService::PAGE_PER)
            ->limit(CustomerService::PAGE_PER)
            ->orderByDesc('id')
            ->get()
            ->toArray();
        $data = [];
        $data['models'] = $models;
        $path = '/api/customer_service/list';
        $simplePaginator = new SimplePaginator($page, CustomerService::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'detail')]
    public function detail(CustomerServiceDetailRequest $request)
    {
        $page = (int) $request->input('page', 0);
        $id = $request->input('id');
        $models = CustomerServiceDetail::where('customer_service_id', $id)
            ->with('user', 'member')
            ->offset($page * CustomerServiceDetail::PAGE_PER)
            ->limit(CustomerServiceDetail::PAGE_PER)
            ->get();
        $data = [];
        $data['models'] = $models;
        $path = '/api/customer_service/detail';
        $simplePaginator = new SimplePaginator($page, CustomerServiceDetail::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(CustomerServiceCreateRequest $request, CustomerServiceService $service)
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
    public function reply(CustomerServiceApiReplyRequest $request, CustomerServiceService $service, ImageService $imageService): PsrResponseInterface
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

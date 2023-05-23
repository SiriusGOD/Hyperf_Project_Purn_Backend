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
use App\Middleware\ApiEncryptMiddleware;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\CustomerService;
use App\Model\CustomerServiceCover;
use App\Model\CustomerServiceDetail;
use App\Service\CustomerServiceService;
use App\Service\ImageService;
use App\Util\General;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class CustomerServiceController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, CustomerServiceService $service)
    {
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', CustomerService::PAGE_PER);
        $memberId = auth()->user()->getId();
        $data = [];
        $data['models'] = $service->list($memberId, $page, $limit, \Hyperf\Support\env('IMAGE_GROUP_ENCRYPT_URL', 'https://new.eewwwn.cn'));
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
        $url = \Hyperf\Support\env('IMAGE_GROUP_ENCRYPT_URL', 'https://new.eewwwn.cn');
        foreach ($models as $model) {
            if (empty($model['image_url'])) {
                $model['image_url'] = "";
                $result[] = $model;
                continue;
            }
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
        $customServiceModel = $service->create([
            'member_id' => $memberId,
            'type' => $request->input('type'),
            'title' => $request->input('title'),
        ]);

        $models = [];
        if ($request->hasFile('cover_one')) {
            $file = $request->file('cover_one');

            $result = General::uploadImage($file);
            if ($result['code'] != 1) {
                $customServiceModel->delete();
                return $this->error(trans('validation.image_upload_error'), 400);
            }
            $model = new CustomerServiceCover();
            $model->customer_service_id = $customServiceModel->id;
            $model->url = $result['url'];
            $model->save();
            $models[] = $model;
        }

        if ($request->hasFile('cover_two')) {
            $file = $request->file('cover_two');
            $result = General::uploadImage($file);
            if ($result['code'] != 1) {
                $service->deleteCovers($models);
                $customServiceModel->delete();
                return $this->error(trans('validation.image_upload_error'), 400);
            }
            $model = new CustomerServiceCover();
            $model->customer_service_id = $customServiceModel->id;
            $model->url = $result['url'];
            $model->save();
            $models[] = $model;
        }

        if ($request->hasFile('cover_three')) {
            $file = $request->file('cover_three');
            $result = General::uploadImage($file);
            if ($result['code'] != 1) {
                $service->deleteCovers($models);
                $customServiceModel->delete();
                return $this->error(trans('validation.image_upload_error'), 400);
            }
            $model = new CustomerServiceCover();
            $model->customer_service_id = $customServiceModel->id;
            $model->url = $result['url'];
            $model->save();
        }

        return $this->success(['id' => $customServiceModel->id]);
    }

    #[RequestMapping(methods: ['POST'], path: 'reply')]
    public function reply(RequestInterface $request, CustomerServiceService $service): PsrResponseInterface
    {
        $userId = auth()->user()->getId();
        $id = (int) $request->input('id');
        $message = $request->input('message', '');
        $params = [
            'id' => $id,
            'member_id' => $userId,
            'message' => $message,
        ];
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $result = General::uploadImage($file);
            $params['image_url'] = $result['url'];
        }
        $service->reply($params);
        $service->setApiDetailRead($id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['POST'], path: 'type/list')]
    public function typeList()
    {
        $result = [];
        foreach (trans('default.customer_service_control.customer_service_type_array') as $key => $value) {
            $key = (int) $key;
            $result[] = [
                'id' => $key,
                'name' => $value,
            ];
        }
        return $this->success($result);
    }
}

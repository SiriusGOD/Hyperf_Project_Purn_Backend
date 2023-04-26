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
use App\Request\ProductApiRequest;
use App\Service\ProductService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class ProductController extends AbstractController
{
    /**
     * 獲取上架中的商品列表.
     */
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(ProductApiRequest $request, ProductService $service)
    {
        $keyword = $request->input('keyword', '');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);
        $result = $service->getListByKeyword($keyword, $offset, $limit);
        $data = [];
        $data['models'] = $result;
        $path = '/api/product/list';
        $simplePaginator = new SimplePaginator($offset, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    /**
     * 獲取上架中的商品數.
     */
    #[RequestMapping(methods: ['GET'], path: 'count')]
    public function count(ProductApiRequest $request, ProductService $service)
    {
        $keyword = $request->input('keyword', '');
        $result = $service->getCount($keyword);
        $data = [
            'count' => $result,
        ];
        return $this->success($data);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Service\ProductService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Request\ProductApiRequest;

/**
 * @Controller
 */
class ProductController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="GET")
     * 獲取上架中的商品列表 
     */
    public function list(ProductApiRequest $request, ProductService $service)
    {
        $keyword = $request->input('keyword','');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);
        $result = $service->getListByKeyword($keyword, $offset,  $limit);
        return $this->success($result);
    }

    /**
     * @RequestMapping(path="count", methods="GET")
     * 獲取上架中的商品數
     */
    public function count(ProductApiRequest $request, ProductService $service)
    {
        $keyword = $request->input('keyword','');
        $result = $service->getCount($keyword);
        $data = array(
            'count' => $result
        );
        return $this->success($data);
    }
}

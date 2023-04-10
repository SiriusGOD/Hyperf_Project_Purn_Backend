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
use App\Service\TagService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class TagController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(RequestInterface $request, TagService $service)
    {
        $data = $service->getTags();
        return $this->success($data->toArray());
    }

    #[RequestMapping(methods: ['GET'], path: 'popular')]
    public function popular(RequestInterface $request, TagService $service)
    {
        $data = $service->getPopularTag();
        return $this->success($data);
    }
}

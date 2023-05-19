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
use App\Service\SystemService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class SystemController extends AbstractController
{
    //手續費
    #[RequestMapping(methods: ['POST'], path: 'withdraw_rate')]
    public function withdraw_rate(SystemService $service)
    {
        $data = $service->memberWithdrawRate();
        
        return $this->success(["models"=> ["rate"=>$data] ]);
    }

    //提領方式
    #[RequestMapping(methods: ['POST'], path: 'withdraw_type')]
    public function withdrawType(SystemService $service)
    {
        $data = $service->memberWithdrawtype();
        foreach ($data as $key => $item) {
            $data[$key]["id"] = (int)$item["id"];
        }
        if(isset($data['id'])){
            unset($data['id']);
        }
        return $this->success(["models"=> $data ]);
    }
}

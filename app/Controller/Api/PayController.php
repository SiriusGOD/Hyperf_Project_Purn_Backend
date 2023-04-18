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
use App\Service\PayService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class PayController extends AbstractController
{
    /**
     * 支付 回調函式.
     */
    #[RequestMapping(methods: ['POST'], path: 'notifyPayAction')]
    public function notifyPayAction(RequestInterface $request, PayService $service)
    {
        $req = $request->all();
        $data = JAddSlashes($req);
        $result = $service->notifyPayAction($data);
        return $this->success(['models' => $result]);
    }
}

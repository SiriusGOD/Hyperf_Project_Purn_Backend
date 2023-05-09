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
use App\Job\MemberHideModelJob;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\Report;
use App\Service\ReportService;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
#[Middleware(ApiAuthMiddleware::class)]
class ReportController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request)
    {
        $data = trans('report.details');
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(RequestInterface $request, ReportService $service,  DriverFactory $factory)
    {
        $memberId = auth('jwt')->user()->getId();
        $type = $request->input('type');
        $modelType = Report::MODEL_TYPE[$request->input('model_type')] ?? Report::MODEL_TYPE['image_group'];
        $modelId = $request->input('model_id');
        $content = $request->input('content', '');
        $status = Report::STATUS['new'];
        if ($type == Report::TYPE['hide']) {
            $status = Report::STATUS['pass'];
        }

        $exist = Report::where('member_id', $memberId)
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->exists();

        if ($exist) {
            return $this->success();
        }

        $service->createReport([
            'member_id' => $memberId,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'content' => $content,
            'type' => $type,
            'status' => $status,
        ]);

        $driver = $factory->get('default');
        $driver->push(new MemberHideModelJob($memberId));

        return $this->success();
    }
}

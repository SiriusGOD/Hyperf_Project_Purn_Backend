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
use App\Model\MemberActivity;
use App\Request\ActivityRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class ActivityController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create(ActivityRequest $request)
    {
        $model = new MemberActivity();
        $model->member_id = auth()->user()->getId();
        $model->last_activity = $request->input('last_activity');
        $model->save();

        return $this->success();
    }
}

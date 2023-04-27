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

use App\Constants\Apicode;
use App\Constants\Constants;
use App\Controller\AbstractController;
use App\Request\StageVideoRequest;
use App\Service\StageVideoService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class StageVideoController extends AbstractController
{
    // 編輯儲存影片分類
    #[RequestMapping(methods: ['POST'], path: 'editStageCate')]
    public function editStageCate(StageVideoRequest $request, StageVideoService $stageVideoService)
    {
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $datas = $request->all();
        $datas['name'] = $request->input('name');
        $datas['member_id'] = $userId;
        $res = $stageVideoService->storeStageVideoCategory($datas);
        if ($res) {
            return $this->success(['msg' => 'success']);
        }
        return $this->success(['msg' => 'faild']);
    }

    // 刪除Stage影片分類
    #[RequestMapping(methods: ['POST'], path: 'deleteStageCate')]
    public function deleteStageCate(RequestInterface $request, StageVideoService $stageVideoService)
    {
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $id = $request->input('id');
        $stageVideoService->delStageVideoCategory($id, $userId);
        return $this->success([]);
    }

    // 新增儲存影片分類
    #[RequestMapping(methods: ['POST'], path: 'createStageCate')]
    public function createStageCate(RequestInterface $request, StageVideoService $stageVideoService)
    {
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $datas['name'] = $request->input('name');
        $datas['member_id'] = $userId;
        $res = $stageVideoService->storeStageVideoCategory($datas);
        return $this->success(['msg' => 'success', 'models' => $res]);
    }

    // 儲存影片
    #[RequestMapping(methods: ['POST'], path: 'stageVideoDefault')]
    public function stageVideoDefault(RequestInterface $request, StageVideoService $stageVideoService)
    {
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $data['video_id'] = $request->input('video_id');
        $data['member_id'] = $userId;
        $stageVideoService->storeStageVideo($data);
        return $this->success([]);
    }

    // 儲存影片
    #[RequestMapping(methods: ['POST'], path: 'stageVideo')]
    public function stageVideo(RequestInterface $request, StageVideoService $stageVideoService)
    {
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $data['video_id'] = $request->input('video_id');
        $data['cate_id'] = $request->input('cate_id');
        $data['member_id'] = $userId;
        $stageVideoService->storeStageVideo($data);
        return $this->success([]);
    }

    // 儲存影片清單
    #[RequestMapping(methods: ['POST'], path: 'stageCateList')]
    public function stageCateList(RequestInterface $request, StageVideoService $stageVideoService)
    {
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $page = (int) $request->input('page', 0);
        $data = [];
        $data['models'] = $stageVideoService->myStageCateList($userId, $page);
        $path = '/api/stage_video/keyword';
        $simplePaginator = new SimplePaginator($page, Constants::DEFAULT_PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }
}

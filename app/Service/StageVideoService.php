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
namespace App\Service;

use App\Model\MemberHasVideo;
use App\Model\MemberHasVideoCategory;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class StageVideoService
{
    public const CACHE_KEY = 'stage_video';

    public const COUNT_KEY = 'stage_video_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

    protected Redis $redis;

    protected $logger;

    protected $memberHasVideo;

    protected $memberHasVideoCategory;

    protected $model;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory, MemberHasVideo $memberHasVideo, MemberHasVideoCategory $memberHasVideoCategory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
        $this->memberHasVideo = $memberHasVideo;
        $this->memberHasVideoCategory = $memberHasVideoCategory;
    }

    // 我收藏的影片
    public function myStageVideo(int $memberId, int $page = 0)
    {
        $model = $this->memberHasVideo->where('member_id', $memberId)->offset(MemberHasVideo::PAGE_PER * $page)->limit(MemberHasVideo::PAGE_PER);
        return $model->get();
    }

    // 收藏影片
    public function storeStageVideo(array $data)
    {
        if (! $this->memberHasVideo->where('member_id', $data['member_id'])->where('video_id', $data['video_id'])->exists()) {
            $model = $this->memberHasVideo;
            $model->video_id = $data['video_id'];
            $model->member_id = $data['member_id'];
            $model->member_has_video_category_id = isset($data['cate_id']) ? $data['cate_id'] : 0;
            if ($model->save()) {
                return true;
            }
            $this->logger->error('error');
            return false;
        }
        return true;
    }

    // del收藏影片
    public function delStageVideo(array $ids)
    {
        if ($this->memberHasVideo->whereIn('id', $ids)->delete()) {
            $this->logger->info('success');
            return true;
        }
        return false;
    }

    // 我收藏的影片分類
    public function myStageCateList(int $memberId, int $page = 0)
    {
        $model = $this->memberHasVideoCategory->where('member_id', $memberId)
            ->offset(MemberHasVideo::PAGE_PER * $page)->limit(MemberHasVideo::PAGE_PER);
        return $model->get();
    }

    // 是否重覆
    public function checkExists(array $datas)
    {
        return $this->memberHasVideoCategory
            ->where('member_id', $datas['member_id'])
            ->where('name', $datas['name'])
            ->exists();
    }

    // 新增/更新 收藏影片分類
    public function storeStageVideoCategory(array $datas)
    {
        $res = self::checkExists($datas);
        if ($res) {
            // 如果存在回傳false
            return false;
        }
        if (isset($datas['id']) && ! empty($datas['id'])) {
            $model = $this->memberHasVideoCategory->where('id', $datas['id'])->first();
            if (! $model) {
                return false;
            }
        } else {
            $model = new $this->memberHasVideoCategory();
        }
        $model->name = $datas['name'];
        $model->member_id = $datas['member_id'];
        $model->save();
        return true;
    }

    // 刪除 收藏影片分類
    public function delStageVideoCategory(int $id, int $memberId)
    {
        $this->memberHasVideoCategory->where('member_id', $memberId)->where('id', $id)->delete();
    }

    // del收藏影片
    public function delStageVideoCate(array $ids)
    {
        if ($this->memberHasVideo->whereIn('id', $ids)->delete()) {
            $this->logger->info('success');
            return true;
        }
        return false;
    }
}

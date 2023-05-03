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
namespace App\Task;

use App\Model\Click;
use App\Model\ImageGroup;
use App\Model\Video;
use App\Service\TagService;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;

#[Crontab(name: 'CalculateTotalClickTask', rule: '0 0 * * *', callback: 'execute', memo: '計算點擊數定時任務')]
class CalculateTotalClickTask
{
    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(TagService $service, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
    }

    public function execute()
    {
        $now = Carbon::now();
        $last = $now->copy()->subDays(30);
        $this->logger->info('開始執行計算點擊數定時任務');
        $models = Click::whereBetween('statistical_date', [$last->toDateString(), $now->toDateString()])
            ->where('type', Video::class)
            ->groupBy('type_id')
            ->select(Db::raw('sum(count) as total'), 'type_id as id')
            ->get();

        foreach ($models as $model) {
            $video = Video::find($model->id);
            $video->total_click = $model->total;
            $video->save();
        }

        $models = Click::whereBetween('statistical_date', [$last->toDateString(), $now->toDateString()])
            ->where('type', ImageGroup::class)
            ->groupBy('type_id')
            ->select(Db::raw('sum(count) as total'), 'type_id as id')
            ->get();

        foreach ($models as $model) {
            $imageGroup = ImageGroup::find($model->id);
            $imageGroup->total_click = $model->total;
            $imageGroup->save();
        }

        $this->logger->info('結束執行計算點擊數定時任務');
    }
}

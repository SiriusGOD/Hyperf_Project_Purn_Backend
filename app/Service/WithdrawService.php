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
use App\Model\MemberWithdraw;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class WithdrawService extends BaseService
{
    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('Order');
    }
    //儲存提現訂單
    public function store(array $data)
    {
        $model = MemberWithdraw::findOrNew($data['id']);
        Db::beginTransaction();
        try {
            foreach ($data as $key => $val) {
                $model->{$key} = $val;
            }
            $model->save();
            Db::commit();
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage(), $data);
            Db::rollBack();
            return false;
        }
        return $model;
    }

}

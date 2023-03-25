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

use App\Model\Product;
use Carbon\Carbon;
use Hamcrest\Type\IsBoolean;
use Hyperf\Redis\Redis;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

class ProductService
{
    public const CACHE_KEY = 'product';
    public const MULTIPLE_CACHE_KEY = 'multiple_cache';

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 更新快取
    public function updateCache(): void
    {
        $now = Carbon::now()->toDateTimeString();
        $result = Product::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('expire', Product::EXPIRE['no'])
            ->get()
            ->toArray();

        $this->redis->set(self::CACHE_KEY, json_encode($result));
    }

    // 新增或更新
    public function store(array $data)
    {
        $model = Product::findOrNew($data['id']);
        $model -> user_id = $data['user_id'];
        $model -> type = $data['type'];
        $model -> correspond_id = $data['correspond_id'];
        $model -> name = $data['name'];
        $model -> expire = $data['expire'];
        $model -> start_time = $data['start_time'];
        $model -> end_time = $data['end_time'];
        $model -> currency = $data['currency'];
        $model -> selling_price = $data['selling_price'];
        $model->save();
    }

    // 軟刪除
    public function delete($id)
    {
        $model = Product::findOrNew($id);
        $model -> deleted_at = Carbon::now()->toDateTimeString();
        $model->save();
    }

    // 新增radis大批匯入的商品ID
    public function insertCache($id)
    {
        $redisKey = self::MULTIPLE_CACHE_KEY . ":" . (int)auth('session')->user()->id;
        $re = $this->redis->lrem($redisKey, 1, (int)$id);
        if($re == 0)$this->redis->lpush($redisKey, $id);
    }
}

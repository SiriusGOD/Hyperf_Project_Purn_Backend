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

use App\Model\Image;
use App\Model\Pay;
use App\Model\MemberLevel;
use App\Model\Coin;
use App\Model\Product;
use App\Model\Tag;
use App\Model\PayCorrespond;
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class ProductService
{
    public const CACHE_KEY = 'product';

    public const MULTIPLE_CACHE_KEY = 'multiple_cache';

    public const TTL_30_Min = 1800;

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
        $model->user_id = $data['user_id'];
        $model->type = $data['type'];
        if ($data['type'] == Product::TYPE_LIST[0] || $data['type'] == Product::TYPE_LIST[1]) {
            $model->diamond_price = Product::DIAMOND_PRICE;
        }
        $model->correspond_id = $data['correspond_id'];
        $model->name = $data['name'];
        $model->expire = $data['expire'];
        $model->start_time = $data['start_time'];
        $model->end_time = $data['end_time'];
        $model->currency = $data['currency'];
        $model->selling_price = $data['selling_price'];
        $model->save();

        if(!empty($data['pay_groups'])){
            // 新增或更新支付方式
            if(! PayCorrespond::where('product_id', $model->id)->whereNull('deleted_at')->exists()){
                // 新增
                foreach ($data['pay_groups'] as $key => $value) {
                    $payment = new PayCorrespond();
                    $payment->product_id = $model->id;
                    $payment->pay_id = $value;
                    $payment->save();
                }
            }else{
                // 更新
                // 撈出目前有設定的支付
                $pays = PayCorrespond::where('product_id', $model->id)->whereNull('deleted_at')->get()->pluck('pay_id')->toArray();

                // 比對要刪除的支付
                $deletes = array_diff($pays, $data['pay_groups']);
                foreach ($deletes as $key => $value) {
                    $payment = PayCorrespond::where('product_id',  $model->id)->where('pay_id', $value)->first();
                    $payment->delete();
                }

                // 比對要新增的支付
                $adds = array_diff($data['pay_groups'], $pays);
                foreach ($adds as $key => $value) {
                    $payment = new PayCorrespond();
                    $payment->product_id = $model->id;
                    $payment->pay_id = $value;
                    $payment->save();
                }
            }
        }
    }

    // 新增radis大批匯入的商品ID
    public function insertCache($id)
    {
        $redisKey = self::MULTIPLE_CACHE_KEY . ':' . (int) auth('session')->user()->id;
        $re = $this->redis->lrem($redisKey, 1, (int) $id);
        if ($re == 0) {
            $this->redis->lpush($redisKey, $id);
        }
    }

    // 獲取商品列表 (會員卡 點數 鑽石)
    public function getListByType($type)
    {
        $checkRedisKey = self::CACHE_KEY . ':' . $type;

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $now = Carbon::now()->toDateTimeString();

        switch ($type) {
            case 'member':
                $query = MemberLevel::join('products', function ($join) {
                            $join->on('member_levels.id', '=', 'products.correspond_id')
                                ->where('products.type', Product::TYPE_LIST[2])
                                ->where('expire', Product::EXPIRE['no']);
                        })->selectRaw('products.id, products.name, products.currency, products.selling_price, products.diamond_price, member_levels.type')->get()->toArray();
                break;
            case 'coin':
                $query = Coin::join('products', function ($join) {
                            $join->on('coins.id', '=', 'products.correspond_id')
                                ->where('products.type', Product::TYPE_LIST[3])
                                ->where('expire', Product::EXPIRE['no']);
                        })->selectRaw('products.id, products.name, products.currency, products.selling_price, products.diamond_price, coins.type')->where('coins.type', Coin::TYPE_LIST[0])->get()->toArray();
                break;
            case 'diamond':
                $query = Coin::join('products', function ($join) {
                            $join->on('coins.id', '=', 'products.correspond_id')
                                ->where('products.type', Product::TYPE_LIST[3])
                                ->where('expire', Product::EXPIRE['no']);
                        })->selectRaw('products.id, products.name, products.currency, products.selling_price, products.diamond_price, coins.type')->where('coins.type', Coin::TYPE_LIST[1])->get()->toArray();
                break;
            default:
                $query = MemberLevel::join('products', function ($join) {
                            $join->on('member_levels.id', '=', 'products.correspond_id')
                                ->where('products.type', Product::TYPE_LIST[2])
                                ->where('expire', Product::EXPIRE['no']);
                        })->selectRaw('products.id, products.name, products.currency, products.selling_price, products.diamond_price, member_levels.type')->get()->toArray();
                break;
        }

        // 撈取個商品的支付方式
        foreach ($query as $key => $value) {
            $pay_query = PayCorrespond::join('pays', 'pays.id', 'pay_corresponds.pay_id')->where('pays.expire', Pay::EXPIRE['no'])->where('pay_corresponds.product_id', $value['id'])->select('pays.id', 'pays.name', 'pays.pronoun')->get()->toArray();
            $query[$key]['pay_method'] = $pay_query;
        }
        return $query;
    }

    // 獲取商品總數 (上架中的)
    public function getCount($keyword)
    {
        if (! empty($keyword)) {
            $tagIds = Tag::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        }

        $query = Product::where('expire', 0);

        if (! empty($tagIds)) {
            $query = Product::join('tag_corresponds', function ($join) {
                $join->on('products.correspond_id', '=', 'tag_corresponds.correspond_id')
                    ->on('products.type', '=', 'tag_corresponds.correspond_type');
            })
                ->whereIn('tag_corresponds.tag_id', $tagIds)
                ->orWhere('products.name', 'like', '%' . $keyword . '%');
        } elseif (! empty($keyword)) {
            $query = $query->where('products.name', 'like', '%' . $keyword . '%');
        }
        return $query->count();
    }
}

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

use App\Model\Coin;
use App\Model\ImageGroup;
use App\Model\MemberLevel;
use App\Model\Pay;
use App\Model\PayCorrespond;
use App\Model\Product;
use App\Model\Report;
use App\Model\Tag;
use App\Model\Video;
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

        if (! empty($data['pay_groups'])) {
            // 新增或更新支付方式
            if (! PayCorrespond::where('product_id', $model->id)->whereNull('deleted_at')->exists()) {
                // 新增
                foreach ($data['pay_groups'] as $key => $value) {
                    $payment = new PayCorrespond();
                    $payment->product_id = $model->id;
                    $payment->pay_id = $value;
                    $payment->save();
                }
            } else {
                // 更新
                // 撈出目前有設定的支付
                $pays = PayCorrespond::where('product_id', $model->id)->whereNull('deleted_at')->get()->pluck('pay_id')->toArray();

                // 比對要刪除的支付
                $deletes = array_diff($pays, $data['pay_groups']);
                foreach ($deletes as $key => $value) {
                    $payment = PayCorrespond::where('product_id', $model->id)->where('pay_id', $value)->first();
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

    // 獲取商品列表 (點數 鑽石)
    public function getListByType($type, $isTW)
    {
        $checkRedisKey = self::CACHE_KEY . ':' . $type .':';
        if($isTW == 1){
            $checkRedisKey .= 'TW';
            $compareCurrency = Product::CURRENCY[3];
        }else{
            $checkRedisKey .= 'OtherRegion'; 
            $compareCurrency = Product::CURRENCY[0];
        }

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $now = Carbon::now()->toDateTimeString();
        
        switch ($type) {
            case 'coin':
                $query = Coin::join('products', function ($join) use ($compareCurrency) {
                    $join->on('coins.id', '=', 'products.correspond_id')
                        ->where('products.type', Product::TYPE_CORRESPOND_LIST['points'])
                        ->where('expire', Product::EXPIRE['no'])
                        ->where('products.currency', $compareCurrency);
                })->selectRaw('products.id, products.name, products.currency, products.selling_price, coins.type, coins.bonus')->where('coins.type', Coin::TYPE_LIST[0])->orderBy('coins.points')->get()->toArray();
                break;
            case 'diamond':
                $query = Coin::join('products', function ($join) {
                    $join->on('coins.id', '=', 'products.correspond_id')
                        ->where('products.type', Product::TYPE_CORRESPOND_LIST['points'])
                        ->where('expire', Product::EXPIRE['no']);
                })->selectRaw('products.id, products.name, products.currency, products.selling_price, coins.type, coins.bonus')->where('coins.type', Coin::TYPE_LIST[1])->orderBy('coins.points')->get()->toArray();
                break;
            default:
                $query = Coin::join('products', function ($join) {
                            $join->on('coins.id', '=', 'products.correspond_id')
                                ->where('products.type', Product::TYPE_CORRESPOND_LIST['points'])
                                ->where('expire', Product::EXPIRE['no']);
                        })->selectRaw('products.id, products.name, products.currency, products.selling_price, coins.type, coins.bonus')->where('coins.type', Coin::TYPE_LIST[0])->orderBy('coins.points')->get()->toArray();
                break;
        }

        // 撈取個商品的支付方式
        foreach ($query as $key => $value) {
            $pay_query = PayCorrespond::join('pays', 'pays.id', 'pay_corresponds.pay_id')->where('pays.expire', Pay::EXPIRE['no'])->where('pay_corresponds.product_id', $value['id'])->select('pays.id', 'pays.name', 'pays.pronoun')->get()->toArray();
            // 插入預設的現金點數支付
            if($type == 'diamond'){
                array_unshift($pay_query,  array(
                    'id' => 0,
                    'name' => trans('api.product_control.pay_coin'),
                    'pronoun' => 'coin'
                ));
            }
            $query[$key]['pay_method'] = $pay_query;
            $query[$key]['selling_price'] = (string)$value['selling_price'];
            if(is_null($query[$key]['bonus'])){
                $query[$key]['bonus'] = null;
            } else {
                $query[$key]['bonus'] = (string) $query[$key]['bonus'];
            }
        }
        $this->redis->set($checkRedisKey, json_encode($query));
        $this->redis->expire($checkRedisKey, self::TTL_30_Min);

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

    // 獲取商品列表 (會員卡)
    public function getListByMember($isTW)
    {   
        $checkRedisKey = self::CACHE_KEY . ':member:';
        if($isTW == 1){
            $checkRedisKey .= 'TW';
            $compareCurrency = Product::CURRENCY[3];
        }else{
            $checkRedisKey .= 'OtherRegion'; 
            $compareCurrency = Product::CURRENCY[0];
        }

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $products = MemberLevel::join('products', function ($join) {
                    $join->on('member_levels.id', '=', 'products.correspond_id')
                        ->where('products.type', Product::TYPE_CORRESPOND_LIST['member'])
                        ->where('expire', Product::EXPIRE['no']);
                })->selectRaw('products.id, products.name, products.currency, products.selling_price, member_levels.type, member_levels.duration, member_levels.title, member_levels.description, member_levels.remark')->orderBy('member_levels.type')->orderBy('member_levels.duration')->get()->toArray();
        
        $vip_arr = [];
        $vip_key = 0;
        $vip_index = 0;
        $diamond_arr = [];
        $diamond_key = 0;
        $diamond_index = 0;
        foreach ($products as $key => $value) {
            // 撈取個商品的支付方式   
            $pay_query = PayCorrespond::join('pays', 'pays.id', 'pay_corresponds.pay_id')->where('pays.expire', Pay::EXPIRE['no'])->where('pay_corresponds.product_id', $value['id'])->select('pays.id', 'pays.name', 'pays.pronoun')->get()->toArray();
            // 插入預設的現金點數支付
            array_unshift($pay_query,  array(
                'id' => 0,
                'name' => trans('api.product_control.pay_coin'),
                'pronoun' => 'coin'
            ));
            if($value['type'] == MemberLevel::TYPE_LIST['0'] && $value['currency'] == $compareCurrency){
                array_push($vip_arr, array(
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'currency' => $value['currency'],
                    'selling_price' => (string)$value['selling_price'],
                    'title' => str_replace('\r', "", $value['title']),
                    'description' => $value['description'],
                    'remark' => $value['remark'],
                    'pay_method' => $pay_query
                ));
                if($value['duration'] == 30){
                    $vip_index = $vip_key;
                }
                $vip_key++;
            }else if($value['currency'] == $compareCurrency){
                array_push($diamond_arr, array(
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'currency' => $value['currency'],
                    'selling_price' => (string)$value['selling_price'],
                    'title' => str_replace('\r', "", $value['title']),
                    'description' => $value['description'],
                    'remark' => $value['remark'],
                    'pay_method' => $pay_query
                ));
                if($value['duration'] == 30){
                    $diamond_index = $diamond_key;
                }
                $diamond_key++;
            }
        }

        $data['vip'] = $vip_arr;
        $data['vip_index'] = $vip_index;
        $data['diamond'] = $diamond_arr;
        $data['diamond_index'] = $diamond_index;

        $this->redis->set($checkRedisKey, json_encode($data));
        $this->redis->expire($checkRedisKey, self::TTL_30_Min);

        return $data;
    }

    public function multipleStore($data)
    {
        $model = Product::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->expire = $data['expire'];
        $model->selling_price = $data['selling_price'];
        $model->save();

        // image
        if($data['type'] == Product::TYPE_LIST[0]){
            $image = ImageGroup::findOrNew($model->correspond_id);
            $image->pay_type = $data['origin_type'];
            $image->save();
        }else{
            //video
            $video = Video::findOrNew($model->correspond_id);
            $video->is_free = $data['origin_type'];
            $video->save();
        }
    }

    public function getEnableIds(string $type)
    {
        $redis = make(Redis::class);
        $key = self::CACHE_KEY;
        $result = [];
        if (!$redis->exists($key)) {
            $this->updateCache();
        }

        $models = json_decode($redis->get($key), true);
        foreach ($models as $model) {
            if($model['type'] == $type) {
                $result[] = $model['correspond_id'];
            }
        }

        return $result;
    }
}

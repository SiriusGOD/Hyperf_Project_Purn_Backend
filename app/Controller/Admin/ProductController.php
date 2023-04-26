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
namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Model\Coin;
use App\Model\ImageGroup;
use App\Model\MemberLevel;
use App\Model\PayCorrespond;
use App\Model\Product;
use App\Model\Video;
use App\Request\ProductMultipleStoreRequest;
use App\Request\ProductRequest;
use App\Service\ProductService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class ProductController extends AbstractController
{
    protected RenderInterface $render;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }

    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        // 顯示幾筆
        $step = Product::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Product::offset(($page - 1) * $step)->limit($step);
        $products = $query->get();
        $query = Product::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.product_control.product_control');
        $data['product_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $products;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/product/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($products, $step, $page);
        $data['paginator'] = $paginator->toArray();
        $data['product_type'] = '';
        return $this->render->render('admin.product.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'expire')]
    public function expire(RequestInterface $request, ResponseInterface $response, ProductService $service): PsrResponseInterface
    {
        $product_type = $request->input('product_type') ? $request->input('product_type') : '';
        $query = Product::where('id', $request->input('id'));
        $record = $query->first();
        if (empty($record)) {
            return $response->redirect('/admin/product/index');
        }
        $record->expire = $request->input('expire', 1);
        $record->save();
        $service->updateCache();
        if (! empty($product_type)) {
            return $response->redirect('/admin/product/search?product_type=' . urlencode($product_type));
        }
        return $response->redirect('/admin/product/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create(RequestInterface $request)
    {
        $id = $request->input('id');
        $product_type = $request->input('product_type');
        if (! empty($product_type)) {
            switch ($product_type) {
                case 'image':
                    $model = ImageGroup::findOrFail($id);
                    // 現金點數 或 鑽石點數(先寫死1顆)
                    $data['currency'] = Product::CURRENCY[1];
                    break;
                case 'video':
                    $model = Video::findOrFail($id);
                    // 現金點數 或 鑽石點數(先寫死1顆)
                    $data['currency'] = Product::CURRENCY[1];
                    break;
                case 'member':
                    $model = MemberLevel::findOrFail($id);
                    // 會員卡 -> 使用現金購買
                    $data['currency'] = Product::CURRENCY[0];
                    break;
                case 'points':
                    $model = Coin::findOrFail($id);
                    if ($model->type == Coin::TYPE_LIST[0]) {
                        // 現金點數 -> 使用現金購買
                        $data['currency'] = Product::CURRENCY[0];
                    }
                    if ($model->type == Coin::TYPE_LIST[1]) {
                        // 鑽石點數 -> 使用現金點數購買
                        $data['currency'] = Product::CURRENCY[1];
                    }
                    break;
            }
        }
        $model->expire = Product::EXPIRE['no'];
        $model->product_id = $model->id;
        $model->id = '';
        $data['navbar'] = trans('default.product_control.product_create');
        $data['product_active'] = 'active';
        $data['pay_ids'] = '';
        $data['model'] = $model;
        $data['product_type'] = $product_type;
        return $this->render->render('admin.product.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'choose')]
    public function choose(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Product::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $product_type = $request->input('product_type');
        $product_name = $request->input('product_name');

        if (! empty($product_type)) {
            switch ($product_type) {
                case 'image':
                    $type_class = Product::TYPE_CORRESPOND_LIST['image'];
                    break;
                case 'video':
                    $type_class = Product::TYPE_CORRESPOND_LIST['video'];
                    break;
                case 'member':
                    $type_class = Product::TYPE_CORRESPOND_LIST['member'];
                    break;
                case 'points':
                    $type_class = Product::TYPE_CORRESPOND_LIST['points'];
                    break;
                default:
                    $type_class = Product::TYPE_CORRESPOND_LIST['image'];
                    break;
            }
            $query = $type_class::select('*');
            $query_total = $type_class::select('*');
            if ($product_type == Product::TYPE_LIST[0] || $product_type == Product::TYPE_LIST[1]) {
                $query = $query->whereNull('deleted_at');
                $query_total = $query_total->whereNull('deleted_at');
            }
            if (! empty($product_name)) {
                $query = $query->where('title', 'like', '%' . $product_name . '%');
                $query_total = $query_total->where('title', 'like', '%' . $product_name . '%');
            }
            $query = $query->offset(($page - 1) * $step)->limit($step);
            $products = $query->get();
            $total = $query_total->count();
            $data['last_page'] = ceil($total / $step);

            foreach ($products as $key => $value) {
                switch ($product_type) {
                    case 'image':
                        $products[$key]->img_thumb = $value->thumbnail;
                        break;
                    case 'video':
                        $products[$key]->img_thumb = $value->cover_thumb;
                        break;
                    case 'member':
                    case 'points':
                        $products[$key]->title = $value->name;
                        break;
                    default:
                        # code...
                        break;
                }
            }
        } else {
            $products = '';
            $total = 0;
        }
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['product_type'] = $product_type;
        $data['navbar'] = trans('default.product_control.product_create');
        $data['product_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $products;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/product/choose';
        $data['next'] = $path . '?page=' . ($page + 1) . '&product_type=' . $product_type . '&product_name=' . $product_name;
        $data['prev'] = $path . '?page=' . ($page - 1) . '&product_type=' . $product_type . '&product_name=' . $product_name;
        $paginator = new Paginator($products, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.product.choose', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(ProductRequest $request, ResponseInterface $response, ProductService $service)
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = (int) auth('session')->user()->id;
        // $data['type'] = Product::TYPE_CORRESPOND_LIST[$request->input('product_type')];
        $data['type'] = $request->input('product_type');
        $data['correspond_id'] = $request->input('product_id') ? $request->input('product_id') : $request->input('correspond_id');
        $data['name'] = $request->input('product_name');
        $data['expire'] = (int) $request->input('expire');
        $data['start_time'] = $request->input('start_time');
        $data['end_time'] = $request->input('end_time');
        $data['currency'] = $request->input('product_currency');
        $data['selling_price'] = $request->input('product_price');
        $data['pay_groups'] = $request->input('pay_groups');
        $service->store($data);
        return $response->redirect('/admin/product/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $model = Product::findOrFail($id);
        $model->title = $model->name;
        $product_type = $model->type;
        switch ($product_type) {
            case 'image':
                // 現金點數 或 鑽石點數(先寫死1顆)
                $data['currency'] = Product::CURRENCY[1];
                break;
            case 'video':
                // 現金點數 或 鑽石點數(先寫死1顆)
                $data['currency'] = Product::CURRENCY[1];
                break;
            case 'member':
                // 會員卡 -> 使用現金購買
                $data['currency'] = Product::CURRENCY[0];
                break;
            case 'points':
                $coin = Coin::findOrFail($model->correspond_id);
                if ($coin->type == Coin::TYPE_LIST[0]) {
                    // 現金點數 -> 使用現金購買
                    $data['currency'] = Product::CURRENCY[0];
                }
                if ($coin->type == Coin::TYPE_LIST[1]) {
                    // 鑽石點數 -> 使用現金點數購買
                    $data['currency'] = Product::CURRENCY[1];
                }
                break;
        }
        $data['model'] = $model;
        $data['product_type'] = $product_type;
        $data['pay_ids'] = PayCorrespond::where('product_id', $id)->get()->pluck('pay_id');
        $data['navbar'] = trans('default.product_control.product_edit');
        $data['product_active'] = 'active';
        return $this->render->render('admin.product.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'multipleChoice')]
    public function multipleChoice(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Product::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $product_type = $request->input('product_type');
        $product_name = $request->input('product_name');
        if (! empty($product_type)) {
            switch ($product_type) {
                case 'image':
                    $type_class = Product::TYPE_CORRESPOND_LIST['image'];
                    break;
                case 'video':
                    $type_class = Product::TYPE_CORRESPOND_LIST['video'];
                    break;
                case 'member':
                    $type_class = Product::TYPE_CORRESPOND_LIST['member'];
                    break;
                case 'points':
                    $type_class = Product::TYPE_CORRESPOND_LIST['points'];
                    break;
                default:
                    $type_class = Product::TYPE_CORRESPOND_LIST['image'];
                    break;
            }
            $query = $type_class::select('*');
            $query_tatal = $type_class::select('*');
            if (! empty($product_name)) {
                $query = $query->where('title', 'like', '%' . $product_name . '%');
                $query_tatal = $query_tatal->where('title', 'like', '%' . $product_name . '%');
            }
            $query = $query->offset(($page - 1) * $step)->limit($step);
            $products = $query->get();
            $total = $query_tatal->count();
            $data['last_page'] = ceil($total / $step);

            foreach ($products as $key => $value) {
                switch ($product_type) {
                    case 'image':
                        $products[$key]->img_thumb = $value->thumbnail;
                        break;
                    case 'video':
                        $products[$key]->img_thumb = $value->cover_thumb;
                        break;
                    case 'member':
                    case 'points':
                        $products[$key]->title = $value->name;
                        break;
                    default:
                        # code...
                        break;
                }
            }
        } else {
            $products = '';
            $total = 0;
        }
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['product_type'] = $product_type;
        $data['navbar'] = trans('default.product_control.product_multiple_create');
        $data['product_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $products;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/product/multipleChoice';
        $data['next'] = $path . '?page=' . ($page + 1) . '&product_type=' . $product_type . '&product_name=' . $product_name;
        $data['prev'] = $path . '?page=' . ($page - 1) . '&product_type=' . $product_type . '&product_name=' . $product_name;
        $paginator = new Paginator($products, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.product.multiplechoice', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'multipleInsert')]
    public function multipleInsert(RequestInterface $request, ProductService $service)
    {
        $insert_data = json_decode($request->input('data'), true);
        $type = urldecode($request->input('type'));

        switch ($type) {
            case 'image':
                $type_class = Product::TYPE_CORRESPOND_LIST['image'];
                // 現金點數 或 鑽石點數(先寫死1顆)
                $data['currency'] = Product::CURRENCY[1];
                break;
            case 'video':
                $type_class = Product::TYPE_CORRESPOND_LIST['video'];
                // 現金點數 或 鑽石點數(先寫死1顆)
                $data['currency'] = Product::CURRENCY[1];
                break;
            case 'member':
                $type_class = Product::TYPE_CORRESPOND_LIST['member'];
                // 會員卡 -> 使用現金購買
                $data['currency'] = Product::CURRENCY[0];
                break;
            case 'points':
                $type_class = Product::TYPE_CORRESPOND_LIST['points'];
                break;
            default:
                $type_class = '';
                break;
        }
        $product_id_arr = [];
        $product_name_arr = [];
        foreach ($insert_data as $key => $value) {
            $model = $type_class::findOrFail($value);
            array_push($product_id_arr, $value);
            if (empty($model->title)) {
                $model->title = $model->name;
            }
            array_push($product_name_arr, $model->title);

            if ($type == 'points') {
                if ($model->type == Coin::TYPE_LIST[0]) {
                    // 現金點數 -> 使用現金購買
                    $data['currency'] = Product::CURRENCY[0];
                }
                if ($model->type == Coin::TYPE_LIST[1]) {
                    // 鑽石點數 -> 使用現金點數購買
                    $data['currency'] = Product::CURRENCY[1];
                }
            }
        }

        $data['model'] = $model;
        $data['product_type'] = $type;
        $data['product_id_arr'] = json_encode($product_id_arr);
        $data['product_name_arr'] = json_encode($product_name_arr);
        $data['navbar'] = trans('default.product_control.product_multiple_create');
        $data['product_active'] = 'active';
        return $this->render->render('admin.product.multipleform', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'multipleStore')]
    public function multipleStore(ProductMultipleStoreRequest $request, ResponseInterface $response, ProductService $service)
    {
        $correspond_id = json_decode($request->input('correspond_id'), true);
        $correspond_name = json_decode($request->input('correspond_name'), true);
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = (int) auth('session')->user()->id;
        // $data['type'] = Product::TYPE_CORRESPOND_LIST[$request->input('product_type')];
        $data['type'] = $request->input('product_type');
        // $data['correspond_id'] = $request->input('product_id') ? $request->input('product_id') : $request->input('correspond_id');
        // $data['name'] = $request->input('product_name');
        $data['expire'] = (int) $request->input('expire');
        $data['start_time'] = $request->input('start_time');
        $data['end_time'] = $request->input('end_time');
        $data['currency'] = $request->input('product_currency');
        $data['selling_price'] = $request->input('product_price');
        foreach ($correspond_id as $key => $value) {
            $data['correspond_id'] = $value;
            $data['name'] = $correspond_name[$key];
            $service->store($data);
        }
        return $response->redirect('/admin/product/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'search')]
    public function search(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Product::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $product_type = $request->input('product_type');
        $product_name = $request->input('product_name');
        if (! empty($product_type)) {
            // switch ($product_type) {
            //     case 'image':
            //         $product_type = Product::TYPE_CORRESPOND_LIST['image'];
            //         break;
            //     case 'video':
            //         $product_type = Product::TYPE_CORRESPOND_LIST['video'];
            //         break;
            //     default:
            //         $product_type = '';
            //         break;
            // }
            $query = Product::select('*')->where('type', $product_type);
            $query_tatal = Product::select('*')->where('type', $product_type);
            if (! empty($product_name)) {
                $query = $query->where('name', 'like', '%' . $product_name . '%');
                $query_tatal = $query_tatal->where('name', 'like', '%' . $product_name . '%');
            }
            $query = $query->offset(($page - 1) * $step)->limit($step);
            $products = $query->get();
            $total = $query_tatal->count();
            $data['last_page'] = ceil($total / $step);
        } else {
            $query = Product::select('*');
            $query_tatal = Product::select('*');
            if (! empty($product_name)) {
                $query = $query->where('name', 'like', '%' . $product_name . '%');
                $query_tatal = $query_tatal->where('name', 'like', '%' . $product_name . '%');
            }
            $query = $query->offset(($page - 1) * $step)->limit($step);
            $products = $query->get();
            $total = $query_tatal->count();
            $data['last_page'] = ceil($total / $step);
        }
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['product_type'] = $product_type;
        $data['navbar'] = trans('default.product_control.product_control');
        $data['product_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $products;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/product/search';
        $data['next'] = $path . '?page=' . ($page + 1) . '&product_type=' . $product_type . '&product_name=' . $product_name;
        $data['prev'] = $path . '?page=' . ($page - 1) . '&product_type=' . $product_type . '&product_name=' . $product_name;
        $paginator = new Paginator($products, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.product.index', $data);
    }
}

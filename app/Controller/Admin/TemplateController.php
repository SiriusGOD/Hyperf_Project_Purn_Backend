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

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
use App\Model\User;
use App\Service\PermissionService;
use App\Service\BaseService;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use PragmaRX\Google2FA\Google2FA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\AllowIPMiddleware')]
class TemplateController extends AbstractController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;
    public $permissionService;
    public $baseService;
    public $main;
    public $fieldsSetting;
    public $render;
  

    function __construct(BaseService $baseService)
    {
        $this->baseService = $baseService;
    //    $this->middleware("permission:$this->main-list|$this->main-create|$this->main-edit|$this->main-delete", ["only" => ["index", "show"]]);
    //    $this->middleware("permission:$this->main-create", ["only" => ["create", "store"]]);
    //    $this->middleware("permission:$this->main-edit", ["only" => ["edit", "update"]]);
    //    $this->middleware("permission:$this->main-delete", ["only" => ["destroy"]]);
    }
    //基本設定 
  
    public function default()
    {
        $data['navbar'] = trans("default.$this->main.title");
        $data['main'] = $this->main;
        $data['fieldsSetting'] = $this->fieldsSetting;
        return $data;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request )
    {
        $data = $this->default();
        $data['page_show'] = strtolower(__FUNCTION__);
        $all = array_filter($request->all() );
        if ($all) {
            //$search = $this->requestService->search($this->fieldsSetting, $this->request);
            //$data['objs'] = $this->entity->where($search)->sortable()->paginate(10);
        } else {
            $data['objs'] = $this->entity->orderBy('id', 'desc')->paginate(10);
        }
        return $this->render->render('admin.template.index', $data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
}


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
use App\Service\RoleService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\View\RenderInterface;

/**
 * @Controller
 */
class indexController extends AbstractController
{
    protected RenderInterface $render;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }

    /**
     * @RequestMapping(path="dashboard", methods={"GET"})
     */
    public function dashboard(RequestInterface $request, RoleService $service)
    {
        $data['navbar'] = '首頁';
        return $this->render->render('admin.index.dashboard', $data);
    }
}

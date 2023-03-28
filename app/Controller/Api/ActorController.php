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

use App\Controller\AbstractController;
use App\Service\ActorService;
use App\Constants\Constants;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller
 */
class ActorController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(RequestInterface $request, ActorService $service)
    {
      $page = (int) $request->input('page', 0);
      $data['models'] = $service->getActors($page);
      $data['page'] = $page;
      $data['step'] = Constants::DEFAULT_PAGE_PER;
      $path = '/api/actor/list';
      $data['next'] = $path . '?page=' . ($page + 1);
      $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
      return $this->success($data);
    }

    /**
     * @RequestMapping(path="count", methods="get")
     */
    public function count(ActorService $service)
    {
      $result = $service->getActorCount();
      return $this->success([
          'count' => $result,
      ]);
    }
}

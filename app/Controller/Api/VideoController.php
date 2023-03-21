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

use App\Service\VideoService;
use App\Service\ObfuscationService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Controller\AbstractController;

/**
 * @Controller
 */
class VideoController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(RequestInterface $request, VideoService $service)
    {
      $offset = $request->input('offset',0);
      $limit = $request->input('limit',0);
      $result = $service->getVideos($offset ,$limit);
      return $this->success($result);
    }

    /**
     * @RequestMapping(path="count", methods="get")
     */
    public function count(VideoService $service)
    {
      $result = $service->getVideoCount();
      return $this->success([$result]);
    }

    /**
     * 回調匯入資料 
     * @RequestMapping(path="data", methods="post")
     */
    public function data(RequestInterface $request, VideoService $service)
    {
      $data = $request->all();
      $result = $service->createVideo($data);
      return $this->success([$result]);
    }

    /**
     * @RequestMapping(path="search", methods="get")
     */
    public function search(RequestInterface $request, VideoService $service)
    {
      $offset = $request->input('offset',0);
      $limit = $request->input('limit',10);
      $name = $request->input('name',"");
      $length = $request->input('length',0);
      $compare = $request->input('compare',0);
      if(empty($name) || strlen($name) ==0){
        $result = ['message'=>'name 不得為空']; 
        return $this->success($result);
      }else{
        $result = $service->searchVideo($name ,$compare ,$length ,$offset ,$limit);
        return $this->success([$result]);
      }
    }
}

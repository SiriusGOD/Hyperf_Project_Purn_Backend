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
namespace App\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Util\CRYPT;
class ApiEncryptMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $response;
    public function __construct(ContainerInterface $container, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $attrs = $request->getAttribute('Hyperf\HttpServer\Router\Dispatched');
        $apis = $attrs->handler->callback;          
        $check = self::exception($apis); 
        
        if(env('PARAMS_ENCRYPT_FLAG') && $check==false ){
            $parsedBody = $request->getParsedBody();
            $data = CRYPT::decrypt($parsedBody['data']);
            $request = $request->withParsedBody(json_decode($data,true)); // 將解密後的數據存儲到請求對象中
        }
        return $handler->handle($request);
    }
    
    //例外URL || API
    public function exception(array $data){
      //自己加QQ
      $exps = [ 
                ['App\Controller\Api\OrderController' ,'list' ],
                ['App\Controller\Api\VideoController' ,'data' ],
               ]; 
      $flag = false;
      foreach($exps as $exp){
        if($data[0] == $exp[0] && $data[1] == $exp[1] ){
          $flag = true; 
        }
      }
      return $flag;
    } 

}

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
use Hyperf\Logger\LoggerFactory;
use App\Util\CRYPT;
class ApiEncryptMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $response;
    protected $encrypt;
    protected $logger;
    public function __construct(LoggerFactory $logger,ContainerInterface $container, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
        $this->logger = $logger;
        $this->encrypt = env('PARAMS_ENCRYPT_FLAG');
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $logger = $this->logger->get('test');
        if($this->encrypt){
            $attrs = $request->getAttribute('Hyperf\HttpServer\Router\Dispatched');
            $data = CRYPT::decrypt($parsedBody['data']);
            $request = $request->withAttribute('data',json_decode($data,true)); // 將解密後的數據存儲到請求對象中
            $logger->info('parse_data : '.json_encode($data));
            $logger->info('attrs:'.json_encode($attrs));
        }else{
            $request = $request->withAttribute('data',$parsedBody ); // 將解密後的數據存儲到請求對象中
        } 
        return $handler->handle($request);
    }
}

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

use Hyperf\Contract\TranslatorInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IpLocationMiddleware implements MiddlewareInterface
{
    protected const CACHE_KEY = "IP_XDB";
    protected ContainerInterface $container;

    protected HttpResponse $response;

    protected Redis $redis;

    protected TranslatorInterface $translator;

    protected \Psr\Log\LoggerInterface $logger;


    public function __construct(Redis $redis, LoggerFactory $factory, TranslatorInterface $translator)
    {
        $this->redis = $redis;
        $this->logger = $factory->get();
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(env("APP_ENV")=="local"){
          return $handler->handle($request);
        }
        $ip = $this->getIP($request->getServerParams());
        $content = \XdbSearcher::loadContentFromFile(BASE_PATH . '/vendor/zoujingli/ip2region/ip2region.xdb');

        $searcher = \XdbSearcher::newWithBuffer($content);

        $region = $searcher->search($ip);
        
        if (empty($region)) {
            return $handler->handle($request);
        }

        $arr = explode("|", $region);
        if (!empty($arr[2]) and $arr[2] == '台湾省') {
            $this->logger->info('ip 為 : ' . $ip . '設定為 zh_TW');
            $this->translator->setLocale('zh_TW');
            $isTW = 1;
        }else{
            $isTW = 0;
        }

        // 將值存儲在請求物件中
        $request = $request->withAttribute('isTW', $isTW);

        return $handler->handle($request);
    }

    public function getIP(array $params)
    {
        if (! empty($params['http_client_ip'])) {
            $ip = $params['http_client_ip'];
        } elseif (! empty($params['http_x_forwarded_for'])) {
            $ip = $params['http_x_forwarded_for'];
        } else {
            if(isset($params['remote_addr']) ){
              $ip = $params['remote_addr'];
            }else{
              return '127.0.0.1';
            }
        }

        return $ip;
    }
}

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

use App\Constants\ErrorCode;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use HyperfExt\Auth\Contracts\AuthManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Swoole\WebSocket\Frame;

/*
 * 获取容器或实例
 */
if (!function_exists('di')) {
    /**
     * Finds an entry of the di by its identifier and returns it.
     * @param null|mixed $id
     * @return ContainerInterface|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function di($id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }
        return $container;
    }
}

/*
 * server 实例 基于 swoole server
 */
if (!function_exists('server')) {
    function server()
    {
        return di()->get(ServerFactory::class)->getServer()->getServer();
    }
}

/*
 * 获取服务
 */
if (!function_exists('service')) {
    /**
     * service
     * 获取服务类实例.
     * @param mixed $key
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function service($key)
    {
        $key = ucfirst($key);
        $fileName = BASE_PATH . "/app/Service/{$key}.php";
        $className = "App\\Service\\{$key}";

        if (file_exists($fileName)) {
            return di($className);
        }

        throw new RuntimeException("服务{$key}不存在，文件不存在！", ErrorCode::SERVER_ERROR);
    }
}

/*
 * 控制台日志
 */
if (!function_exists('stdLog')) {
    function stdLog()
    {
        return di()->get(StdoutLoggerInterface::class);
    }
}

/*
 * 文件日志
 */
if (!function_exists('logger')) {
    /**
     * @param mixed $name
     * @param mixed $group
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function logger($name = 'log', $group = 'default')
    {
        return di()->get(LoggerFactory::class)->get($name, $group);
    }
}

/*
 * redis 客户端实例
 */
if (!function_exists('redis')) {
    function redis()
    {
        return di()->get(Hyperf\Redis\Redis::class);
    }
}

/*
 * 缓存实例 简单的缓存
 */
if (!function_exists('cache')) {
    function cache()
    {
        return di()->get(CacheInterface::class);
    }
}

/*
 * 将可抛出对象格式化为字符串
 */
if (!function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

/*
 * 将job推送到异步队列
 */
if (!function_exists('queue_push')) {
    /**
     * Push a job to async queue.
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        return di()->get(DriverFactory::class)->get($key)->push($job, $delay);
    }
}

/*
 * 不可逆加密
 */
if (!function_exists('encrypt_with_salt')) {
    /**
     * 加密.
     * @param mixed $str
     * @param mixed $salt
     */
    function encrypt_with_salt($str, $salt): string
    {
        return md5(md5($str) . $salt);
    }
}

/*
 * 可逆加密
 */
if (!function_exists('encrypt')) {
    /**
     * 加密函数.
     * @param string $str 加密前的字符串
     * @param string $key 密钥
     * @return string 加密后的字符串
     */
    function encrypt(string $str, string $key = ''): string
    {
        $coded = '';
        $length = strlen($key);

        for ($i = 0, $count = strlen($str); $i < $count; $i += $length) {
            $coded .= substr($str, $i, $length) ^ $key;
        }

        return str_replace('=', '', base64_encode($coded));
    }
}

/*
 * 可逆解密
 */
if (!function_exists('decrypt')) {
    /**
     * 解密函数.
     * @param string $str 加密后的字符串
     * @param string $key 密钥
     * @return string 加密前的字符串
     */
    function decrypt(string $str, string $key = ''): string
    {
        $coded = '';
        $length = strlen($key);
        $str = base64_decode($str);

        for ($i = 0, $count = strlen($str); $i < $count; $i += $length) {
            $coded .= substr($str, $i, $length) ^ $key;
        }

        return $coded;
    }
}

/*
 * 校验密码复杂度
 */
if (!function_exists('valid_pass')) {
    function valid_pass($password): array
    {
        // $r1 = '/[A-Z]/';  //uppercase
        $r2 = '/[A-z]/';  // lowercase
        $r3 = '/[0-9]/';  // numbers
        $r4 = '/[~!@#$%^&*()\-_=+{};:<,.>?]/';  // special char

        /*if (preg_match_all($r1, $candidate, $o) < 1) {
            $msg =  "密码必须包含至少一个大写字母，请返回修改！";
            return FALSE;
        }*/
        if (preg_match_all($r2, $password, $o) < 1) {
            $msg = '密码必须包含至少一个字母，请返回修改！';
            return ['code' => -1, 'msg' => $msg];
        }
        if (preg_match_all($r3, $password, $o) < 1) {
            $msg = '密码必须包含至少一个数字，请返回修改！';
            return ['code' => -1, 'msg' => $msg];
        }
        /*if (preg_match_all($r4, $candidate, $o) < 1) {
            $msg =  "密码必须包含至少一个特殊符号：[~!@#$%^&*()\-_=+{};:<,.>?]，请返回修改！";
            return FALSE;
        }*/
        if (strlen($password) < 8) {
            $msg = '密码必须包含至少含有8个字符，请返回修改！';
            return ['code' => -1, 'msg' => $msg];
        }
        return ['code' => 0, 'msg' => 'success'];
    }
}

/*
 * 检查手机号码格式
 * @param string $mobile 手机号码
 */
if (!function_exists('check_mobile')) {
    function check_mobile($mobile): bool
    {
        return preg_match('/1[3-9]\d{9}$/', $mobile) || preg_match('/000\d{8}$/', $mobile);
    }
}

/*
 * 计算总页数
 */
if (!function_exists('page')) {
    /**
     * 计算总页数等.
     * @param mixed $totalCount
     */
    function page($totalCount, int $pageSize = 10, int $currPage = 1): array
    {
        if ($totalCount > 0) {
            $totalPage = ceil($totalCount / $pageSize);
        } else {
            $totalPage = 0;
        }

        if ($currPage <= 0 || $currPage > $totalPage) {
            $currPage = 1;
        }

        $startCount = ($currPage - 1) * $pageSize;
        return [$totalPage, $startCount];
    }
}

/*
 * redis 客户端实例
 */
if (!function_exists('redis')) {
    function redis()
    {
        return di()->get(Redis::class);
    }
}

/*
 * websocket frame 实例
 */
if (!function_exists('frame')) {
    function frame()
    {
        return di()->get(Frame::class);
    }
}

/*
 * 缓存实例 简单的缓存
 */
if (!function_exists('cache')) {
    function cache()
    {
        return di()->get(Psr\SimpleCache\CacheInterface::class);
    }
}

/*
 * 获取request实例
 */
if (!function_exists('request')) {
    function request()
    {
        return di()->get(ServerRequestInterface::class);
    }
}

/*
 * 获取response实例
 */
if (!function_exists('response')) {
    function response()
    {
        return di()->get(ResponseInterface::class);
    }
}

if (!function_exists('auth')) {
    /**
     * Auth认证辅助方法.
     * @return mixed
     */
    function auth(string $guard = null)
    {
        if (is_null($guard)) {
            $guard = config('auth.default.guard');
        }
        return make(AuthManagerInterface::class)->guard($guard);
    }
}

if (!function_exists('convert_bytes')) {
    function convert_bytes($number): string
    {
        $number = (string)$number;
        $len = strlen($number);
        if ($len < 4) {
            return sprintf('%d b', $number);
        }
        if ($len >= 4 && $len <= 6) {
            return sprintf('%0.2f Kb', $number / 1024);
        }
        if ($len >= 7 && $len <= 9) {
            return sprintf('%0.2f Mb', $number / 1024 / 1024);
        }
        return sprintf('%0.2f Gb', $number / 1024 / 1024 / 1024);
    }
}




//使用者的權限
if (!function_exists('authPermission')) {
    function authPermission(string $key)
    {
        $service = di(\App\Service\PermissionService::class);
        return $service->checkPermission($key);
    }
}

if (!function_exists('calc_bytes')) {
    function calc_bytes($size, $digits = 2): string
    {
        if (!$size) {
            return '';
        }
        $unit = ['', 'K', 'M', 'G', 'T', 'P'];
        $base = 1024;
        $i = floor(log($size, $base));
        $n = count($unit);
        if ($i >= $n) {
            $i = $n - 1;
        }
        return round($size / ($base ** $i), $digits) . ' ' . $unit[$i] . 'B';
    }
}

// 權限文字
if (! function_exists('cutStrLang')) {
    function cutStrLang(string $str)
    {
        $result = trans('roles.special_title');
        // 特殊字
        if (! empty($result[$str])) {
            return $result[$str];
        }
        $collect = explode('-', $str);
        return __("default.titles.{$collect[0]}") . __("default.{$collect[1]}");
    }
}
// 判斷是否在array中
if (!function_exists('checkInAryRtnStr')) {
    function checkInAryRtnStr(int $id, array $array, string $string): string
    {
        return in_array($id, $array) ? $string : '';
    }
}
// 寫入LOG
if (!function_exists('checkUserSession')) {
    function checkUserSession()
    {
        $all = di(\Hyperf\Contract\SessionInterface::class)->all();
        if (!isset($all['auth_session'])) {
            return false;
        }
        return true;
    }
}

// Debgu show log
if (!function_exists('debugLog')) {
    function debugLog($data)
    {
        if (is_array($data)) {
            stdLog()->log('debug', json_encode($data));
        } else {
            stdLog()->log('debug', $data);
        }
    }
}


// redis鎖
if (!function_exists('redisLock')) {
    function redisLock($key)
    {
        return redis()->setnx($key, 1) && redis()->expire($key, 10);
    }
}

// 隨幾產生數字
if (!function_exists('randInt')) {
    function randInt(int $count)
    {
        $c = '';
        for ($i = 1; $i <= $count; ++$i) {
            $c .= rand(0, 9);
        }
        return $c;
    }
}

// 從REDIS 取得 sites
if (!function_exists('getSitesUrl')) {
    function getSitesUrl($siteId)
    {
        $sites = di(\App\Service\SiteService::class)->getSites();
        $url = '';
        foreach ($sites as $key => $site) {
            if ($site['id'] == $siteId) {
                $url = $site['url'];
            }
        }
        if ($url == '') {
            return false;
        }
        return $url;
    }
}

// redis 是否存在
if (!function_exists('redisExists')) {
    function redisExists($key)
    {
        if (redis()->exists($key)) {
            return redis()->get($key);
        }
        return false;
    }
}
// 日誌
if (!function_exists('setLog')) {
    function setLog()
    {
        return di(Hyperf\Logger\LoggerFactory::class)->get();
    }
}

// 判斷IP 是否在Array
if (!function_exists('ipInArray')) {
    function ipInArray($ip, $allowIps)
    {
        if (in_array($ip, $allowIps)) {
            return true;
        }

        return false;
    }
}

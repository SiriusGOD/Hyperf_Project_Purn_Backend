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

use GuzzleHttp\Client;
use Hyperf\Logger\LoggerFactory;

class MailService
{
    public $log;

    public function __construct(LoggerFactory $factory)
    {
        $this->log = $factory->get('email', 'email');
    }
    // TODO finish email
    public function send(string $address, string $subject, string $content): bool
    {
        $mail = [];
        $mail['app_name'] = env('SYSTEM_ID');
        $mail['email'] = $address;
        $mail['subject'] = $subject;
        $mail['body'] = $content;
        ksort($mail);
        $string = '';
        foreach ($mail as $key => $datum) {
            $string .= "{$key}={$datum}&";
        }
        $string .= 'key=' . env('PAY_SIGNKEY');
        $mail['sign'] = md5($string);
        $client = new Client();
        $body = $client->post(env('EMAIL_HOST'), [
            'json' => $mail,
            'http_errors' => false,
        ]);

        $this->log->info('send to ' . $address . ':' . $body->getBody()->getContents());

        return true;
    }
}

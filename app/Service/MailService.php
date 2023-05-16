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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // TODO finish email
    public function send_test(string $address, string $subject, string $content): bool
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                      // Enable verbose debug output
            $mail->isSMTP();                                           // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                      // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                  // Enable SMTP authentication
            $mail->Username   = 'victor20220919@gmail.com';         // SMTP username
            $mail->Password   = '2uuiajgi';                  // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('@gmail.com', 'Your Name');
            $mail->addAddress('recipient@example.com', 'Recipient Name');     // Add a recipient

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Test Email';
            $mail->Body    = 'This is a test email.';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
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

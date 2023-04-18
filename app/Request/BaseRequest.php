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
namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class BaseRequest extends FormRequest
{
    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->getInputData();
        $res = data_get($data, $key, $default);
        print_r($res);
        return $res;
    }
}

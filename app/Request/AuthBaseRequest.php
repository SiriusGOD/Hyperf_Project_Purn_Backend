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

use App\Service\UserService;
use Hyperf\Redis\Redis;
use Hyperf\Validation\Request\FormRequest;

class AuthBaseRequest extends FormRequest
{
    public function failedAuthorization()
    {
        throw new \App\Exception\UnauthorizedException(403, trans('validation.authorize'));
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth('session')->check()) {
            return true;
        }

        return false;
    }
}

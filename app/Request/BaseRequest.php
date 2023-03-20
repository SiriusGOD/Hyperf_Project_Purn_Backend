<?php

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class BaseRequest extends FormRequest
{
    public function failedAuthorization()
    {
        throw new \App\Exception\UnauthorizedException(403, trans('validation.authorize'));
    }
}
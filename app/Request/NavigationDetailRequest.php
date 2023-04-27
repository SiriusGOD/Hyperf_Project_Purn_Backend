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

class NavigationDetailRequest extends AuthApiBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'page' => 'numeric',
            'limit' => 'numeric',
            'id' => 'required|numeric',
            'nav_id' => 'required|numeric|between:0,3',
        ];
    }
}

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

class VideoApiSearchRequest extends AuthApiBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'page' => 'numeric',
            'limit' => 'numeric',
            'sort_by' => 'numeric',
            'is_asc' => 'numeric',
            'compare' => 'numeric',
            'length' => 'numeric',
        ];
    }
}

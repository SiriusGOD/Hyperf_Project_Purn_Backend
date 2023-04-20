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

use Hyperf\Validation\Rule;

class ImageRequest extends AuthBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|between:1,50',
            'group_id' => 'numeric',
            'id' => 'numeric',
            'hot_order' => 'numeric',
            'image' => [
                Rule::requiredIf(function () {
                    return empty($this->input('id'));
                }),
            ],
        ];

        return $rules;
    }
}

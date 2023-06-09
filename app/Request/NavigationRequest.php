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

class NavigationRequest extends AuthBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = 0;
        if (! empty($this->input('id'))) {
            $id = $this->input('id');
        }

        return [
            'id' => 'required|numeric',
            'name' => 'required|string|between:1,50',
            'hot_order' => [
                'required',
                'numeric',
                Rule::unique('navigations')->ignore($id),
            ],
        ];
    }
}

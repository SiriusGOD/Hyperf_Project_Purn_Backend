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

class MemberApiUpdateRequest extends AuthApiBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = 0;
        if (auth('jwt')->check()) {
            $id = auth('jwt')->user()->getId();
        }
        if (! empty($this->input('id'))) {
            $id = $this->input('id');
        }

        return [
            'id' => 'numeric|exists:members',
            'name' => [
                'string',
                Rule::unique('members')->ignore($id),
            ],
            'password' => 'string',
            'account' => [
                'string',
                Rule::unique('members')->ignore($id),
            ],
            'sex' => 'numeric',
            'age' => 'numeric|between:18,130',
            'phone' => 'numeric',
            'address' => 'string',
            'uuid' => [
                'string',
                Rule::unique('members')->ignore($id),
            ],
        ];
    }
}

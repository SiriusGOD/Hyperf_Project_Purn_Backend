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
use Hyperf\Validation\Rule;

class UserUpdateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $redis = make(Redis::class);
        $token = $redis->get(UserService::CACHE_KEY . auth()->user()->getId());

        if (auth('jwt')->check() and $this->header('Authorization') == 'Bearer ' . $token) {
            return true;
        }

        if (auth('session')->check()) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = 0;
        if(auth()->check()) {
            $id = auth()->user()->getId();
        }
        if (!empty($this->input('id'))) {
            $id = $this->input('id');
        }

        $rules = [
            'id' => 'numeric|exists:users',
            'name' => [
                'string',
                Rule::unique('users')->ignore($id)
            ],
            'password' => 'string',
            'email' => [
                'string',
                Rule::unique('users')->ignore($id)
            ],
            'sex' => 'numeric',
            'age' => 'numeric|between:18,130',
            'phone' => 'numeric',
            'address' => 'string',
            'uuid' => [
                'string',
                Rule::unique('users')->ignore($id)
            ]
        ];

        return $rules;
    }
}

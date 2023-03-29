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

use App\Service\MemberService;
use Hyperf\Redis\Redis;
use Hyperf\Validation\Rule;

class MemberUpdateRequest extends AuthBaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $redis = make(Redis::class);
        $token = $redis->get(MemberService::CACHE_KEY . auth('jwt_member')->user()->getId());

        if (auth('jwt_member')->check() and $this->header('Authorization') == 'Bearer ' . $token) {
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
        if (auth('jwt_member')->check()) {
            $id = auth('jwt_member')->user()->getId();
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
            'email' => [
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

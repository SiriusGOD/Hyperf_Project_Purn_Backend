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
use App\Model\Role;
use App\Model\User;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class UserDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleIds = Role::where('type', Role::TYPE['api'])->get()->pluck('id')->toArray();
        $roleIds[] = Role::API_DEFAULT_USER_ROLE_ID;
        $rules = [
            'id' => [
                'required',
                'numeric',
                Rule::exists('users')->whereIn('role_id', $roleIds)->where('status', User::STATUS['NORMAL'])
            ]
        ];

        return $rules;
    }
}

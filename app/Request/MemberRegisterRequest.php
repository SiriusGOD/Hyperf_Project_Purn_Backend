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

use Hyperf\Validation\Request\FormRequest;

class MemberRegisterRequest extends FormRequest
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
        return [
            'name' => 'string|unique:members',
            'password' => 'required|string',
            'email' => 'email|unique:members',
            'sex' => 'numeric',
            'age' => 'numeric|between:18,130',
            'phone' => 'numeric',
            'address' => 'string',
            'uuid' => 'required|string|unique:members',
        ];
    }
}

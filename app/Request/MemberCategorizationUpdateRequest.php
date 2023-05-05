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

use App\Model\User;
use Hyperf\Validation\Request\FormRequest;

class MemberCategorizationUpdateRequest extends AuthApiBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
            'name' => 'required|string',
            'hot_order' => 'required|numeric',
            'is_default' => 'required|numeric',
        ];
    }
}

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

class MemberCategorizationDetailCreateRequest extends AuthApiBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'member_categorization_id' => 'required|numeric',
            'type' => 'required|numeric|in:1,2',
            'type_id' => 'required|numeric',
        ];
    }
}

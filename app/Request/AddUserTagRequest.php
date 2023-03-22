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
use Hyperf\Validation\Rule;

class AddUserTagRequest extends AuthBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'tags.*' => 'required|numeric',
        ];

        return $rules;
    }
}

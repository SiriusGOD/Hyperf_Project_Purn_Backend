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

use App\Model\MemberFollow;
use Hyperf\Validation\Rule;

class AddMemberFollowRequest extends AuthApiBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
            'type' => ['required', Rule::in([
                MemberFollow::TYPE_LIST[0],
                MemberFollow::TYPE_LIST[1],
                MemberFollow::TYPE_LIST[2],
                MemberFollow::TYPE_LIST[3],
            ])],
        ];
    }
}

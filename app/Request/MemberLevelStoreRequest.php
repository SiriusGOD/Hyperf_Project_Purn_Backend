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

use App\Model\MemberLevel;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class MemberLevelStoreRequest extends FormRequest
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
            'id' => 'numeric',
            'type' => ['required', Rule::in([
                MemberLevel::TYPE_LIST[0],
                MemberLevel::TYPE_LIST[1],
            ])],
            'name' => 'required|max:255',
            'duration' => 'required|numeric',
        ];
    }
}

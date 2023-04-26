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

use App\Model\Pay;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class PayStoreRequest extends FormRequest
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
            'name' => 'required|max:255',
            'pronoun' => 'required|max:255',
            'expire' => ['required', Rule::in([
                Pay::EXPIRE['no'],
                Pay::EXPIRE['yes'],
            ])],
        ];
    }
}

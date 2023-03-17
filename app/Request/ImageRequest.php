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
use Hyperf\Validation\Rule;

class ImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth('jwt')->check()) {
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
        $rules = [
            'title' => 'required|string|between:1,50',
            'group_id' => 'numeric',
            'id' => 'numeric',
            'image' => Rule::requiredIf(function() {
                return empty($this->input('id'));
            })
        ];

        return $rules;
    }
}

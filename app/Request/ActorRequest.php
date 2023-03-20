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
use App\Traits\SitePermissionTrait;
use Hyperf\Validation\Request\FormRequest;
class ActorRequest extends FormRequest
{
    use SitePermissionTrait;

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
        $rules = [
            'id' => 'numeric',
            'user_id' => 'numeric',
            'name' => 'required|max:255',
            'sex' => 'required|max:255',
        ];

        return $rules;
    }
}


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

use Hyperf\Validation\Rule;

class TagRequest extends AuthBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = 0;
        if (! empty($this->input('id'))) {
            $id = $this->input('id');
        }

        $rule['name'] = 'required|string|between:1,50';
        
        if(! empty($this->input('hot_order'))){
            $rule['hot_order'] = [
                'required',
                'numeric',
                Rule::unique('tags')->ignore($id),
            ];
        }

        return $rule;
    }
}

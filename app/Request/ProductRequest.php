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

use App\Model\Product;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class ProductRequest extends FormRequest
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
        $rule_arr = [
            'id' => 'numeric',
            'product_type' => 'required|max:255',
            'product_id' => 'numeric',
            'correspond_id' => 'numeric',
            'product_name' => 'required|max:255',
            'expire' => [
                'required',
                Rule::in([
                    Product::EXPIRE['no'],
                    Product::EXPIRE['yes'],
                ]),
            ],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_date',
            'product_currency' => 'required|max:255',
            'product_price' => 'required|numeric'
        ];
        if($this->input('product_type') == Product::TYPE_LIST[2] || $this->input('product_type') == Product::TYPE_LIST[3]){
            $rule_arr['pay_groups'] = 'required|array';
        }

        return $rule_arr;
    }
}

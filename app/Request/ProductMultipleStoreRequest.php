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

class ProductMultipleStoreRequest extends FormRequest
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
            'product_type' => 'required|max:255',
            'correspond_id' => 'required|json',
            'correspond_name' => 'required|json',
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
            'product_price' => 'required|numeric',
        ];
    }
}

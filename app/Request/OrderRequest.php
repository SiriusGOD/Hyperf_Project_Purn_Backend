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

use App\Model\Order;
use App\Service\UserService;
use Hyperf\Redis\Redis;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class OrderRequest extends AuthBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'user_id' => 'required|numeric',
            'order_status' => Rule::in([
                Order::ORDER_STATUS['create'],
                Order::ORDER_STATUS['delete'],
                Order::ORDER_STATUS['finish'],
            ]),
            'offset' => 'numeric',
            'limit' => 'numeric',
            'product_id' => 'numeric',
        ];

        return $rules;
    }
}
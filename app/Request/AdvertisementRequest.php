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

use App\Model\Advertisement;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class AdvertisementRequest extends FormRequest
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
        $rules = [
            'id' => 'numeric',
            'user_id' => 'numeric',
            'name' => 'required|max:255',
            'url' => 'required|max:255',
            'position' => ['required', Rule::in([
                Advertisement::POSITION['top_banner'],
                Advertisement::POSITION['bottom_banner'],
                Advertisement::POSITION['popup_window'],
                Advertisement::POSITION['ad_image'],
                Advertisement::POSITION['ad_link'],
            ])],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_date',
            'buyer' => 'required|max:255',
            'expire' => [
                'required',
                Rule::in([
                    Advertisement::EXPIRE['no'],
                    Advertisement::EXPIRE['yes'],
                ]),
            ]
        ];

        return $rules;
    }
}

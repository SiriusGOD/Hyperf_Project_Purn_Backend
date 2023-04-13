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

use App\Model\Announcement;

class AnnouncementRequest extends AuthBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $statusTypes = Announcement::STATUS;
        $statusStr = implode(',', array_values($statusTypes));
        var_dump($statusStr);
        return [
            'title' => 'required|string|between:1,50',
            'content' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_date',
            'status' => 'required|in:' . $statusStr,
        ];
    }
}

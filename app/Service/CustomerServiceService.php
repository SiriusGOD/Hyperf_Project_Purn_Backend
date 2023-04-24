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
namespace App\Service;

use App\Model\CustomerService;
use App\Model\CustomerServiceDetail;

class CustomerServiceService
{
    public function reply(array $params): void
    {
        $model = new CustomerServiceDetail();
        $model->customer_service_id = $params['id'];
        $model->member_id = $params['member_id'] ?? null;
        $model->user_id = $params['user_id'] ?? null;
        $model->message = $params['message'];
        if (! empty($params['image_url'])) {
            $model->image_url = $params['image_url'];
        }
        $model->is_read = 0;
        $model->save();
    }

    public function create(array $params): CustomerService
    {
        $model = new CustomerService();
        $model->member_id = $params['member_id'];
        $model->type = $params['type'];
        $model->title = $params['title'];
        $model->is_unread = 0;
        $model->save();

        return $model;
    }

    public function setMainUnRead(int $id): void
    {
        $model = CustomerService::find($id);
        $model->is_unread = 1;
        $model->save();
    }

    public function setAdminDetailRead(int $id): void
    {
        CustomerServiceDetail::where('customer_service_id', $id)
            ->whereNotNull('member_id')
            ->update([
                'is_read' => 1,
            ]);
    }

    public function setApiDetailRead(int $id): void
    {
        CustomerServiceDetail::where('customer_service_id', $id)
            ->whereNotNull('user_id')
            ->update([
                'is_read' => 1,
            ]);
    }
}

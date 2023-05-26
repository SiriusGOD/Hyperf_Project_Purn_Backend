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
        $model->save();

        return $model;
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

    public function list(int $memberId, int $page, int $limit, string $url): array
    {
        $models = CustomerService::where('member_id', $memberId)
            ->with('customerServiceCovers')
            ->offset($page * $limit)
            ->limit($limit)
            ->orderByDesc('id')
            ->get()
            ->toArray();

        $result = [];
        foreach ($models as $model) {
            foreach ($model['customer_service_covers'] as $key => $row) {
                $model['customer_service_covers'][$key]['url'] = $url . $row['url'];
                $imageInfo = getimagesize($url . $row['url']);
                $model['customer_service_covers'][$key]['height'] = $imageInfo[1] ?? 0;
                $model['customer_service_covers'][$key]['weight'] = $imageInfo[0] ?? 0;
            }
            $result[] = $model;
        }

        return $result;
    }

    public function deleteCovers(array $models): void
    {
        foreach ($models as $model) {
            $model->delete();
        }
    }
}

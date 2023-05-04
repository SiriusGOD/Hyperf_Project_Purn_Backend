<?php

namespace App\Service;

use App\Model\MemberCategorization;
use App\Model\MemberCategorizationDetail;

class MemberCategorizationService
{
    public function createMemberCategorization(array $params) : void
    {
        $model = new MemberCategorization();
        $model->member_id = $params['member_id'];
        $model->name = $params['name'];
        $model->hot_order = $params['hot_order'] ?? 0;
        $model->is_default = $params['is_default'] ?? 0;
        $model->save();
    }

    public function createMemberCategorizationDetail(array $params) : void
    {
        $exist = MemberCategorizationDetail::where('member_categorization_id', $params['member_categorization_id'])
            ->where('type', $params['type'])
            ->where('type_id', $params['type_id'])
            ->exists();

        if ($exist) {
           return;
        }

        $model = new MemberCategorizationDetail;
        $model->member_categorization_id = $params['member_categorization_id'];
        $model->type = $params['type'];
        $model->type_id = $params['type_id'];
        $model->total_click = 0;
        $model->save();
    }
}
<?php

namespace App\Service;

use App\Model\Click;
use Carbon\Carbon;

class ClickService
{
    public function addClick(string $type, int $typeId)
    {
        $today = Carbon::now()->toDateString();
        $model = Click::where('type', $type)
            ->where('type_id', $typeId)
            ->where('statistical_date', $today)
            ->first();

        if (empty($model)) {
            $model = $this->createClick([
                'type' => $type,
                'type_id' => $typeId,
                'statistical_date' => $today
            ]);
        }

        $model->count++;
        $model->save();

    }

    private function createClick(array $data)
    {
        $model = new Click();
        $model->type = $data['type'];
        $model->type_id = $data['type_id'];
        $model->statistical_date = $data['statistical_date'];
        $model->count = 0;

        return $model;
    }
}
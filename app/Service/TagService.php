<?php

namespace App\Service;

use App\Model\Tag;
use Hyperf\Database\Model\Collection;

class TagService
{
    public function getTags() : Collection
    {
        return Tag::all();
    }

    public function createTag(string $name, int $userId) : void
    {
        $model = new Tag();
        $model->name = $name;
        $model->user_id = $userId;
        $model->save();
    }
}
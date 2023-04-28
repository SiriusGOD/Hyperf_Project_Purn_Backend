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
use App\Model\MemberCashAccount;

class MemberCashAccountService extends BaseService
{
    public function store(array $data)
    {
        $model = MemberCashAccount::findOrNew($data['id']);
        $this->modelStore($model, $data); 
        return $model;
    }

}

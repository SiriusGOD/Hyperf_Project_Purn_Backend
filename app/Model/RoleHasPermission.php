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
namespace App\Model;

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class RoleHasPermission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'role_has_permissions';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'role_id' => 'integer', 'permission_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

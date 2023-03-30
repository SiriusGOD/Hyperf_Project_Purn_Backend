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

use Hyperf\DbConnection\Model\Model;
use Qbhy\HyperfAuth\Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $password
 * @property int $sex
 * @property int $age
 * @property string $avatar
 * @property string $email
 * @property string $phone
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $role_id
 * @property string $uuid
 */
class Member extends Model implements Authenticatable
{
    public const STATUS = ['NORMAL' => 1, 'DISABLE' => 2, 'DELETE' => 3];

    public const SEX = ['DEFAULT' => 0, 'MALE' => 1, 'FEMALE' => 2];

    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     * @var string
     */
    protected ?string $table = 'members';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected array $fillable = ['name', 'sex', 'age', 'password', 'role_id'];

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'sex' => 'integer', 'age' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'role_id' => 'integer'];

    protected array $hidden = ['password'];

    public function getJwtIdentifier()
    {
        return $this->getKey();
    }

    public function getId()
    {
        // 返回用户id
        return $this->id;
    }

    public static function retrieveById($key): ?Authenticatable
    {
        // 通过id查找用户
        return Member::find($key);
    }

    /**
     * JWT自定义载荷.
     */
    public function getJwtCustomClaims(): array
    {
        return ['guard' => 'api'];
    }
}

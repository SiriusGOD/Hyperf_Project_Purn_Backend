<?php

declare (strict_types=1);
namespace App\Model;

use Carbon\Carbon;

/**
 * @property int $id 
 * @property string $name
 * @property int $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Role extends Model
{
    public const SUPER_ADMIN = 1;

    public const API_DEFAULT_USER_ROLE_ID = 0;

    public const TYPE = [
        'ADMIN' => 0,
        'API' => 1
    ];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    static public function getTypeNameByType(int $type)
    {
        $typeFlip = array_flip(self::TYPE);
        return strtolower($typeFlip[$type]);
    }
}
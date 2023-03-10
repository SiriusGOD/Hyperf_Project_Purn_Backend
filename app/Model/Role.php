<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id
 * @property string $keywords
 */
class Role extends Model
{
    public const SUPER_ADMIN = 1;
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
    protected $casts = ['id' => 'integer'];
}
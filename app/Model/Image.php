<?php

declare (strict_types=1);
namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property int $id 
 * @property int $user_id 
 * @property string $title 
 * @property string $title_thumbnail 
 * @property string $thumbnail 
 * @property string $url 
 * @property int $likes 
 * @property int $group_id 
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Image extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'images';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'likes' => 'integer', 'group_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
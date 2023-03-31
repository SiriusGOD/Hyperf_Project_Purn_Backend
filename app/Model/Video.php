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

use Hyperf\Database\Model\SoftDeletes;

/**
 * @property User $user
 * @property \Hyperf\Database\Model\Collection|Tag[] $tags
 */
class Video extends Model
{
    use SoftDeletes;

    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'videos';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'correspond', 'tag_corresponds', 'correspond_id');
    }
}

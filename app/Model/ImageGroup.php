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

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $thumbnail
 * @property string $url
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property \Hyperf\Database\Model\Collection|Image[] $images
 */
class ImageGroup extends Model
{
    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'image_groups';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function images()
    {
        return $this->hasMany(Image::class, 'group_id');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'correspond', 'tag_corresponds', 'correspond_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

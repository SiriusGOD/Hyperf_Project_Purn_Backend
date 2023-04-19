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
 * @property int $pay_type 
 * @property-read \Hyperf\Database\Model\Collection|Image[] $images 
 * @property-read \Hyperf\Database\Model\Collection|Tag[] $tags 
 * @property-read User $user 
 * @property-read mixed $model_type 
 */
class ImageGroup extends Model
{
    public const PAGE_PER = 10;

    public string $modelType = self::class;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'image_groups';

    public const DEFAULT_FREE_LIMIT = 3;

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'pay_type' => 'integer'];

    protected array $appends = ['model_type'];

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

    protected function getModelTypeAttribute()
    {
        return self::class;
    }

    public function imagesLimit()
    {
        return $this->hasMany(Image::class, 'group_id')->limit(self::DEFAULT_FREE_LIMIT);
    }
}

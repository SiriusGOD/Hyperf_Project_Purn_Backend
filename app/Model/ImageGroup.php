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
 * @property-read \Hyperf\Database\Model\Collection|Image[] $images 
 * @property-read \Hyperf\Database\Model\Collection|Tag[] $tags 
 * @property-read User $user 
 * @property-read \Hyperf\Database\Model\Collection|Image[] $imagesLimit 
 * @property-read mixed $model_type 
 * @property-read mixed $image_count 
 */
class ImageGroup extends Model
{
    use SoftDeletes;
    public const PAGE_PER = 10;

    public const DEFAULT_FREE_LIMIT = 3;

    public const IMAGE_GROUP_PAY_TYPE = [
        'free' => 0,
        'vip' => 1
    ];

    public string $modelType = self::class;

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
    protected array $casts = [];

    protected array $appends = ['model_type', 'image_count', 'point'];

    public function images()
    {
        return $this->hasMany(Image::class, 'group_id')->withTrashed();
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'correspond', 'tag_corresponds', 'correspond_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function imagesLimit()
    {
        return $this->hasMany(Image::class, 'group_id');
    }

    public function getAdminBaseUrl()
    {
        if ($this->sync_id > 0) {
            return env('IMAGE_GROUP_DECRYPT_URL');
        }

        return '';
    }

    public function getApiBaseUrl()
    {
        if ($this->sync_id > 0) {
            return env('IMAGE_GROUP_ENCRYPT_URL');
        }

        return '';
    }

    protected function getModelTypeAttribute()
    {
        return 'image_group';
    }

    protected function getImageCountAttribute()
    {
        return $this->images()->count();
    }

    protected function getPointAttribute()
    {
        return "0";
    }
}

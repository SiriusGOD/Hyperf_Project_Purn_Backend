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
 * @property int $hot_order
 * @property int $sync_id
 * @property \Hyperf\Database\Model\Collection|Image[] $images
 * @property \Hyperf\Database\Model\Collection|Tag[] $tags
 * @property User $user
 * @property \Hyperf\Database\Model\Collection|Image[] $imagesLimit
 * @property mixed $model_type
 */
class ImageGroup extends Model
{
    public const PAGE_PER = 10;

    public const DEFAULT_FREE_LIMIT = 3;

    public const IMAGE_GROUP_PAY_TYPE = [
        'free' => 0,
        'vip' => 1,
        'diamond' => 2,
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
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'pay_type' => 'integer', 'hot_order' => 'integer', 'sync_id' => 'integer'];

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

    public function imagesLimit()
    {
        return $this->hasMany(Image::class, 'group_id')->limit(self::DEFAULT_FREE_LIMIT);
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
        return self::class;
    }
}

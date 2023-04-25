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
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $thumbnail
 * @property string $url
 * @property string $description
 * @property int $group_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property int $sync_id
 * @property User $user
 */
class Image extends Model
{
    use SoftDeletes;

    public const PAGE_PER = 15;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'images';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'group_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'sync_id' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}

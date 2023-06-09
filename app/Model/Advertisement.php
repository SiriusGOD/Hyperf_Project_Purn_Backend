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
 * @property string $name 
 * @property string $image_url 
 * @property string $url 
 * @property int $position 
 * @property string $start_time 
 * @property string $end_time 
 * @property string $buyer 
 * @property int $expire 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property int $height 
 * @property int $weight 
 * @property-read mixed $model_type 
 */
class Advertisement extends Model
{
    public const POSITION = ['banner' => 1, 'ad_full' => 2, 'popup_window' => 3, 'ad_image' => 4];

    public const EXPIRE = ['no' => 0, 'yes' => 1];

    public const PAGE_PER = 10;

    protected array $appends = ['model_type'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'advertisements';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'position' => 'integer', 'expire' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'height' => 'integer', 'weight' => 'integer'];

    protected function getModelTypeAttribute()
    {
        return 'advertisement';
    }
}

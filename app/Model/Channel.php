<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property string $name 
 * @property string $url 
 * @property string $params 
 * @property string $image 
 * @property string $amount 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Channel extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'channels';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 欄位基本設定 
     */
    public function tableFieldsSetting()
    {
        return  [
            'name' => [
                'type' => 'text',
                'required' => true,
                'search' => true,
                'search' => [
                    'level' => 'like'
                ],
            ],            
            'url' => [
                'type' => 'text',
                'required' => false,
                'search' => true,
            ],
            'params' => [
                'type' => 'text',
                'required' => true,
                'index_show'=>false
            ],
            'amount' => [
                'type' => 'number',
                'required' => true,
            ]
        ];
    }

}

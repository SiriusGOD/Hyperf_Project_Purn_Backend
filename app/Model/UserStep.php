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
 * @property string $user_name
 * @property int $user_id
 * @property int $role_id
 * @property string $action
 * @property string $comment
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserStep extends Model
{
    public const PAGE_PER = 10;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_steps';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'role_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    /**
     * 欄位基本設定 
     */
    public function tableFieldsSetting()
    {
        return  [
            'user_name' => [
                'type' => 'text',
                'required' => true,
                'search' => true,
                'search' => [
                    'level' => 'like'
                ],
            ],            
            'user_id' => [
                'type' => 'text',
                'required' => false,
                'search' => true,
            ],
            'action' => [
                'type' => 'text',
                'required' => false,
            ],
            'comment' => [
                'type' => 'text',
                'required' => true,
            ]
        ];
    }
}

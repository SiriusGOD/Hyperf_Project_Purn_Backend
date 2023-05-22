<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $tag_id 
 * @property string $name 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class ImportTag extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'import_tags';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'tag_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

<?php

declare(strict_types=1);

namespace App\Model;



/**
 * @property int $id 
 * @property int $actor_id 
 * @property int $actor_classifications_id 
 */
class ActorHasClassification extends Model
{
    public const PAGE_PER = 10;
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'actor_has_classifications';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'actor_id' => 'integer', 'actor_classifications_id' => 'integer'];
}

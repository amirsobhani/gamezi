<?php

namespace Modules\Game\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @method static global()
 */
class Game extends Model
{
    const GameIndexName = 'game_index';

    const Global = 1, Private = 0;

    protected $table = 'game';

    protected $fillable = [
        'rawg_id',
        'esrb_rating_id',
        'slug',
        'name',
        'description',
        'description_en',
        'metacritic',
        'metacritic_url',
        'background_image',
        'rating',
        'ratings',
        'rating_top',
        'playtime',
        'website',
        'image',
        'release_time',
        'use_count',
        'suggestions_count',
        'is_global',
        'tags'
    ];

    protected $casts = [
        'ratings' => 'array'
    ];
    public function developer()
    {
        return $this->belongsToMany(Developer::class, 'game_developers');
    }

    public function publisher()
    {
        return $this->belongsToMany(Publisher::class, 'game_publishers');
    }

    public function genre()
    {
        return $this->belongsToMany(Genre::class, 'game_genres');
    }

    public function esrbRating()
    {
        return $this->belongsTo(EsrbRating::class);
    }
    public function Platform()
    {
        return $this->belongsToMany(Platform::class, 'game_platforms');
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'entity', 'media_relations');
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', self::Global);
    }
}

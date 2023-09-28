<?php


namespace Modules\Game\Entities;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = [
        'rawg_id',
        'platform_category_id',
        'name',
        'slug',
        'alias',
        'description',
        'release_time',
        'image'
    ];

    public $timestamps = false;

    public function game()
    {
        return $this->belongsToMany(Game::class, 'game_platforms');
    }

    public function category()
    {
        return $this->belongsTo(PlatformCategory::class);
    }
}

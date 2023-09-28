<?php


namespace Modules\Game\Entities;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = [
        'title',
        'fa_title',
    ];

    public $timestamps = false;

    public function game()
    {
        return $this->belongsToMany(Game::class, 'game_genres');
    }

}

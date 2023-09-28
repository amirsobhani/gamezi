<?php

namespace Modules\Game\Entities;


use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    protected $fillable = [
        'rawg_id',
        'alias',
        'name',
        'logo',
        'description',
        'established_date'
    ];

    public function game()
    {
        return $this->belongsToMany(Game::class, 'game_developers');
    }
}

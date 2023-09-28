<?php


namespace Modules\Game\Entities;

use Illuminate\Database\Eloquent\Model;

class EsrbRating extends Model
{
    protected $fillable = [
        'title',
        'icon',
        'start_age',
        'description',
    ];

    public function game()
    {
        return $this->hasMany(Game::class);
    }
}

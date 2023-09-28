<?php


namespace Modules\Game\Entities;

use Illuminate\Database\Eloquent\Model;

class PlatformCategory extends Model
{
    protected $fillable = [
        'name',
        'fa_name',
        'slug',
        'icon',
        'theme',
    ];

    public function platform()
    {
        return $this->hasMany(Platform::class);
    }

}

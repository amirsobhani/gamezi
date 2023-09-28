<?php

namespace Modules\Game\Entities;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    const ImageType = 0, VideoType = 1;

    protected $fillable = [
        'bucket',
        'name',
        'file_name',
        'path',
        'type',
        'mime_type',
        'format',
        'size',
        'preview',
    ];
}

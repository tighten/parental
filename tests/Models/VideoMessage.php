<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parental\HasParent;

class VideoMessage extends Message
{
    use HasParent;

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}

<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Parental\HasParent;

class TextMessage extends Message
{
    use HasParent;

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}

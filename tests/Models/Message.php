<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parental\HasChildren;

class Message extends Model
{
    use HasChildren;

    protected $guarded = [];

    protected $childTypes = [
        1 => TextMessage::class,
        2 => VideoMessage::class,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}

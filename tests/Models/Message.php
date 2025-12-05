<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

class Message extends Model
{
    use HasChildren;

    protected $guarded = [];

    protected $childTypes = [
        1 => TextMessage::class,
        2 => VideoMessage::class,
    ];
}

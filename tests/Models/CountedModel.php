<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\Tests\Traits\CountsCreatedModels;

class CountedModel extends Model
{
    use CountsCreatedModels;

    static int $created = 0;

    protected $fillable = [
        'type', 'name',
    ];

    protected $guarded = [];
}

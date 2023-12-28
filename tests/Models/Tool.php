<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;
use Parental\Tests\Enums\ToolNames;

class Tool extends Model
{
    use HasChildren;

    protected $fillable = ['type'];

    protected function childTypes(): array
    {
        return [
            ToolNames::ClawHammer->value => ClawHammer::class,
            ToolNames::Mallet->value => Mallet::class,
            ToolNames::SledgeHammer->value => SledgeHammer::class,
          ];
    }
}

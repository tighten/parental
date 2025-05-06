<?php

namespace Parental\Tests\Traits;

use Parental\Tests\Observers\ModelCreatedObserver;

trait CountsCreatedModels
{
    public static function bootCountsCreatedModels(): void
    {
        static::observe(new ModelCreatedObserver);
    }
}

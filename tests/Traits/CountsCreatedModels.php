<?php

namespace Parental\Tests\Traits;

use Parental\Tests\Observers\ModelCreatedObserver;

trait CountsCreatedModels
{
    static int $created = 0;

    public static function bootCountsCreatedModels(): void
    {
        static::observe(new ModelCreatedObserver());
    }
}

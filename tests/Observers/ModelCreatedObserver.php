<?php

namespace Parental\Tests\Observers;

use Parental\Tests\Models\CountedModel;

class ModelCreatedObserver
{
    public function created(CountedModel $model)
    {
        $model::$created++;
    }
}

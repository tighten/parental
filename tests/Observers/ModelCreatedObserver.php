<?php

namespace Parental\Tests\Observers;

use Parental\Tests\Models\CountedModel;

class ModelCreatedObserver
{
    /**
     * Handle the created event.
     *
     * @param  CountedModel  $model
     *
     * @return void
     */
    public function created(CountedModel $model)
    {
        $model::$created++;
    }
}

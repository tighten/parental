<?php

namespace Parental\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Parental\HasChildren;
use Parental\HasParent;

class NovaResourceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if (class_exists(Nova::class)) {
            Nova::serving(function () {
                $this->setNovaResources();
            });
        }
    }

    /**
     * @return void
     */
    protected function setNovaResources(): void
    {
        $map = [];
        foreach (Nova::$resources as $resource) {
            $parent = $resource::$model;
            $map[$parent] = $resource;
            $traits = class_uses_recursive($parent);
            if (isset($traits[HasChildren::class]) && ! isset($traits[HasParent::class])) {
                foreach ((new $parent)->getChildTypes() as $child) {
                    if (! isset($map[$child])) {
                        $map[$child] = $resource;
                    }
                }
            }
        }
        Nova::$resourcesByModel = array_merge($map, Nova::$resourcesByModel);
    }
}

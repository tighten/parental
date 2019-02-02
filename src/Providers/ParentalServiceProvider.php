<?php

namespace Tightenco\Parental\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Tightenco\Parental\Commands\DiscoverChildren;
use Tightenco\Parental\HasChildren;
use Tightenco\Parental\HasParent;

class ParentalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (class_exists(Nova::class)) {
           $this->extendParentNovaResourcesToChildren();
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../../config/parental.php' => config_path('parental.php')]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/parental.php', 'parental');
        $this->commands([DiscoverChildren::class]);
    }

    protected function extendParentNovaResourcesToChildren()
    {
        // We want to ensure that this bit of code runs after resources are registered in Nova
        $this->app->booted(function () {
            Nova::serving(function () {
                $this->setNovaResources();
            });
        });
    }

    protected function setNovaResources()
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

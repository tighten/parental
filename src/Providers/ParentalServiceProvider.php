<?php

namespace Parental\Providers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class ParentalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bindEagerLoadingMacros();
    }

    private function bindEagerLoadingMacros(): void
    {
        Collection::macro('loadChildren', function (array $childrenRelationsMap) {
            /** @var \Illuminate\Database\Eloquent\Collection $this */
            $this->groupBy(fn (Model $model) => get_class($model))
                ->each(fn ($models, string $className) => Collection::make($models)->load($childrenRelationsMap[$className] ?? []));

            return $this;
        });

        Collection::macro('loadChildrenCount', function (array $childrenRelationsMap) {
            /** @var \Illuminate\Database\Eloquent\Collection $this */
            $this->groupBy(fn (Model $model) => get_class($model))
                ->each(fn ($models, string $className) => Collection::make($models)->loadCount($childrenRelationsMap[$className] ?? []));

            return $this;
        });
    }
}

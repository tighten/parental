<?php

namespace Tightenco\Parental;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Arr;

class ParentScope implements Scope
{
    protected static $registered = [];

    public function apply(Builder $builder, Model $parent)
    {
        if (! isset(static::$registered[get_class($parent)])) {
            return;
        }

        $builder->where(function (Builder $builder) use ($parent) {
            $childColumn = $parent->getInheritanceColumn();

            $existingImplementations = $parent->getGlobalScopes();

            foreach (static::$registered[get_class($parent)] as $alias => $implementations) {
                $builder->orWhere(function (Builder $builder) use ($alias, $childColumn, $implementations, $existingImplementations) {
                    $builder->where($childColumn, $alias);

                    foreach ($implementations as $key => $implementation) {
                        if (Arr::has($existingImplementations, str_replace($alias.':', '', $key))) {
                            continue;
                        }

                        $this->applyImplementation($builder, $implementation);
                    }
                });
            }

            $builder->orWhere(function (Builder $builder) use ($parent, $childColumn) {
                $missingChildren = array_diff_key($parent->getChildTypes(), static::$registered[get_class($parent)]);
                $builder->orWhereIn($childColumn, array_keys($missingChildren))
                        ->orWhereNull($childColumn);
            });
        });
    }

    protected function applyImplementation(Builder $builder, $implementation)
    {
        $builder->where(function (Builder $builder) use ($implementation) {
            if ($implementation instanceof \Closure) {
                ($implementation)($builder);
            } elseif ($implementation instanceof Scope) {
                $implementation->apply($builder, $builder->getModel());
            }
        });
    }

    public static function registerChild(Model $child, string $key, $implementation)
    {
        static::$registered[get_parent_class($child)][$child->classToAlias(get_class($child))][$key] = $implementation;
    }
}

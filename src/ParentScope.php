<?php

namespace Tightenco\Parental;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

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

            foreach (static::$registered[get_class($parent)] as $alias => $implementations) {
                foreach ($implementations as $implementation) {
                    $builder->orWhere(function (Builder $builder) use ($childColumn, $alias, $implementation) {
                        $builder->where($childColumn, $alias);

                        if ($implementation instanceof \Closure) {
                            ($implementation)($builder);
                        } elseif ($implementation instanceof Scope) {
                            $implementation->apply($builder, $builder->getModel());
                        }
                    });
                }
            }

            $builder->orWhere(function (Builder $builder) use ($parent, $childColumn) {
                $missingChildren = array_diff_key($parent->getChildTypes(), static::$registered[get_class($parent)]);
                $builder->orWhereIn($childColumn, array_keys($missingChildren))
                        ->orWhereNull($childColumn);
            });
        });
    }

    public static function registerChild(Model $child, $implementation)
    {
        static::$registered[get_parent_class($child)][$child->classToAlias(get_class($child))][] = $implementation;
    }
}

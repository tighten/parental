<?php

namespace Tightenco\Parental;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ParentScope
{
    protected $child;
    protected $implementation;

    protected static $registered = [];

    protected function __construct(Model $child, $implementation)
    {
        $this->child = $child;
        $this->implementation = $implementation;
    }

    protected function apply(Builder $builder)
    {
        $builder->orWhere($this->child->getInheritanceColumn(), $this->child->classToAlias(get_class($this->child)));

        if ($this->implementation instanceof \Closure) {
            ($this->implementation)($builder);
        } elseif ($this->implementation instanceof Scope) {
            $this->implementation->apply($builder, $builder->getModel());
        }
    }

    public static function passToParent(string $parent, Model $child, $implementation)
    {
        static::$registered[$parent][] = get_class($child);

        return function (Builder $builder) use ($child, $implementation) {
            (new static($child, $implementation))->apply($builder);
        };
    }

    public static function addMissingChildren(string $parent)
    {
        return function (Builder $builder) use ($parent) {
            if (! isset(static::$registered[$parent])) {
                return;
            }

            $instance = new $parent;
            $missingChildren = array_diff($instance->getChildTypes(), static::$registered[$parent]);

            $builder->orWhereIn($instance->getInheritanceColumn(), array_keys($missingChildren))
                    ->orWhereNull($instance->getInheritanceColumn());
        };
    }
}

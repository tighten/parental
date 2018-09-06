<?php

namespace Tightenco\Parental;

use Illuminate\Support\Str;
use ReflectionClass;

trait HasParentModel
{
    public $hasParentModel = true;

    public static function bootHasParentModel()
    {
        static::creating(function ($model) {
            if ($model->parentHasReturnsChildModelsTrait()) {
                $model->forceFill(
                    [$model->getInhertanceColumn() => $model->classToAlias(get_class($model))]
                );
            }
        });

        static::addGlobalScope(function ($query) {
            $instance = new static;

            if ($instance->parentHasReturnsChildModelsTrait()) {
                $query->where($instance->getInhertanceColumn(), $instance->classToAlias(get_class($instance)));
            }
        });
    }

    public function parentHasReturnsChildModelsTrait()
    {
        return $this->returnsChildModels ?? false;
    }

    public function getTable()
    {
        if (! isset($this->table)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this->getParentClass()))));
        }

        return $this->table;
    }

    public function getForeignKey()
    {
        return Str::snake(class_basename($this->getParentClass())).'_'.$this->primaryKey;
    }

    public function joiningTable($related, $instance = null)
    {
        $relatedClassName = method_exists((new $related), 'getClassNameForRelationships')
            ? (new $related)->getClassNameForRelationships()
            : class_basename($related);

        $models = [
            Str::snake($relatedClassName),
            Str::snake($this->getClassNameForRelationships()),
        ];

        sort($models);

        return strtolower(implode('_', $models));
    }

    public function getClassNameForRelationships()
    {
        return class_basename($this->getParentClass());
    }

    protected function getParentClass()
    {
        static $parentClassName;

        return $parentClassName ?: $parentClassName = (new ReflectionClass($this))->getParentClass()->getName();
    }
}

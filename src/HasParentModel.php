<?php

namespace Tightenco\Parental;

use Illuminate\Support\Str;
use ReflectionClass;

trait HasParentModel
{
    public $hasParentModel = true;

    public static function bootHasParentModel()
    {
        static::addGlobalScope(function ($query) {
            $instance = new static;

            if ($instance->parentHasReturnsChildModelsTrait()) {
                $query->where($instance->getInhertanceColumn(), get_class($instance));
            }
        });
    }

    public function newInstance($attributes = [], $exists = false)
    {
        if ($this->parentHasReturnsChildModelsTrait()) {
            $attributes = $this->setTypeColumn($attributes);
        }

        return parent::newInstance($attributes, $exists);
    }

    public function parentHasReturnsChildModelsTrait()
    {
        return $this->returnsChildModels ?? false;
    }

    public function setTypeColumn($attributes)
    {
        if (! isset($attributes[$this->getInhertanceColumn()]) && ! $this->exists) {
            $attributes[$this->getInhertanceColumn()] = get_class($this);
        }

        return $attributes;
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

    public function joiningTable($related)
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
        return (new ReflectionClass($this))->getParentClass()->getName();
    }
}

<?php

namespace ModelFamily;

use Illuminate\Support\Str;
use ReflectionClass;

trait HasParentModel
{
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
        $models = [
            Str::snake(class_basename($related)),
            Str::snake(class_basename($this->getParentClass())),
        ];

        sort($models);

        return strtolower(implode('_', $models));
    }

    protected function getParentClass()
    {
        return (new ReflectionClass($this))->getParentClass()->getName();
    }
}

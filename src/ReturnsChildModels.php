<?php

namespace Tightenco\Parental;

use Illuminate\Support\Str;

trait ReturnsChildModels
{
    protected $returnsChildModels = true;

    public function newInstance($attributes = [], $exists = false)
    {
        $model = isset($attributes[$this->getInhertanceColumn()])
            ? $this->getChildModel($attributes)
            : new static(((array) $attributes));

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        return $model;
    }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->newInstance((array) $attributes, true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey) && $instance->hasParentModel) {
            $foreignKey = Str::snake($instance->getClassNameForRelationships()).'_'.$instance->getKeyName();
        }

        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        return parent::hasMany($related, $foreignKey, $localKey);
    }

    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($table) && $instance->hasParentModel) {
            $table = $this->joiningTable($instance->getClassNameForRelationships());
        }

        return parent::belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
    }

    public function getClassNameForRelationships()
    {
        return class_basename($this);
    }

    public function getInhertanceColumn()
    {
        return $this->childTypeColumn ?: 'type';
    }

    protected function getChildModel(array $attributes)
    {
        $className = $this->classFromAlias(
            $attributes[$this->getInhertanceColumn()]
        );

        return new $className((array)$attributes);
    }

    public function classFromAlias($aliasOrClass)
    {
        if (property_exists($this, 'childTypeAliases')) {
            if (isset($this->childTypeAliases[$aliasOrClass])) {
                return $this->childTypeAliases[$aliasOrClass];
            }
        }

        return $aliasOrClass;
    }

    public function classToAlias($className)
    {
        if (property_exists($this, 'childTypeAliases')) {
            if (in_array($className, $this->childTypeAliases)) {
                return array_search($className, $this->childTypeAliases);
            }
        }

        return $className;
    }
}

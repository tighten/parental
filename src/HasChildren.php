<?php

namespace Parental;

use Closure;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

trait HasChildren
{
    /**
     * @var bool
     */
    protected static $parentBootMethods;

    /**
     * @var bool
     */
    protected $hasChildren = true;

    /**
     * Register a model event with the dispatcher.
     *
     * @param  string  $event
     * @param Closure|string  $callback
     * @return void
     */
    protected static function registerModelEvent($event, $callback): void
    {
        parent::registerModelEvent($event, $callback);

        $childTypes = (new self)->getChildTypes();

        if (static::class === self::class && $childTypes !== []) {
            // We don't want to register the callbacks that happen in the boot method of the parent, as they'll be called
            // from the child's boot method as well.
            if (! self::parentIsBooting()) {
                foreach ($childTypes as $childClass) {
                    if ($childClass !== self::class) {
                        $childClass::registerModelEvent($event, $callback);
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected static function parentIsBooting(): bool
    {
        if (! isset(self::$parentBootMethods)) {
            self::$parentBootMethods[] = 'boot';

            foreach (class_uses_recursive(self::class) as $trait) {
                self::$parentBootMethods[] = 'boot'.class_basename($trait);
            }

            self::$parentBootMethods = array_flip(self::$parentBootMethods);
        }

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            $class = isset($trace['class']) ? $trace['class'] : null;
            $function = isset($trace['function']) ? $trace['function'] : '';

            if ($class === self::class && isset(self::$parentBootMethods[$function])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false): self
    {
        $model = isset($attributes[$this->getInheritanceColumn()])
            ? $this->getChildModel($attributes)
            : new static(((array) $attributes));

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        return $model;
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null): self
    {
        $attributes = (array) $attributes;

        $inheritanceAttributes = [];
        $inheritanceColumn = $this->getInheritanceColumn();

        if (isset($attributes[$inheritanceColumn])) {
            $inheritanceAttributes[$inheritanceColumn] = $attributes[$inheritanceColumn];
        }

        $model = $this->newInstance($inheritanceAttributes, true);

        $model->setRawAttributes($attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $ownerKey
     * @param  string|null  $relation
     * @return BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null): BelongsTo
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey) && $instance->hasParent) {
            $foreignKey = Str::snake($instance->getClassNameForHasChildrenRelationships()).'_'.$instance->getKeyName();
        }

        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null): HasMany
    {
        return parent::hasMany($related, $foreignKey, $localKey);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param  string  $related
     * @param  string|null  $table
     * @param  string|null  $foreignPivotKey
     * @param  string|null  $relatedPivotKey
     * @param  string|null  $parentKey
     * @param  string|null  $relatedKey
     * @param  string|null  $relation
     * @return BelongsToMany
     */
    public function belongsToMany(
        $related, $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $relation = null
    ): BelongsToMany {
        $instance = $this->newRelatedInstance($related);

        if (is_null($table) && $instance->hasParent) {
            $table = $this->joiningTable($instance->getClassNameForHasChildrenRelationships());
        }

        return parent::belongsToMany(
            $related,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation,
        );
    }

    /**
     * @return string
     */
    public function getClassNameForHasChildrenRelationships(): string
    {
        return class_basename($this);
    }

    /**
     * @return string
     */
    public function getInheritanceColumn(): string
    {
        return property_exists($this, 'childColumn') ? $this->childColumn : 'type';
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    protected function getChildModel(array $attributes)
    {
        $className = $this->classFromAlias(
            $attributes[$this->getInheritanceColumn()]
        );

        return new $className((array) $attributes);
    }

    /**
     * @param mixed $aliasOrClass
     * @return string
     */
    public function classFromAlias($aliasOrClass): string
    {
        $childTypes = $this->getChildTypes();

        if (isset($childTypes[$aliasOrClass])) {
            return $childTypes[$aliasOrClass];
        }

        return $aliasOrClass;
    }

    /**
     * @param string $className
     * @return string
     */
    public function classToAlias(string $className): string
    {
        $childTypes = $this->getChildTypes();

        if (in_array($className, $childTypes)) {
            return array_search($className, $childTypes);
        }

        return $className;
    }

    /**
     * @return array
     */
    public function getChildTypes(): array
    {
        return property_exists($this, 'childTypes') ? $this->childTypes : [];
    }
}

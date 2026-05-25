<?php

namespace Parental;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Events\QueuedClosure;
use Illuminate\Support\Str;
use Parental\Exceptions\EagerLoadingException;
use UnitEnum;

/**
 * @method static EloquentBuilder childrenWith(array $relations) Eager-loads relations on single-table inheritance models.
 * @method static EloquentBuilder childrenWith(array $relations) Eager-loads relations on single-table inheritance models.
 */
trait HasChildren
{
    /**
     * @var bool
     */
    protected $hasChildren = true;

    /**
     * Register a becoming model event with the dispatcher.
     *
     * @param  QueuedClosure|callable|array|class-string  $callback
     */
    public static function becoming($callback): void
    {
        static::registerModelEvent('becoming', $callback);
    }

    /**
     * Register a model event with the dispatcher.
     *
     * @param  string  $event
     * @param  QueuedClosure|callable|array|class-string  $callback
     */
    protected static function registerModelEvent($event, $callback): void
    {
        parent::registerModelEvent($event, $callback);

        // Only the parent class should forward events to children. When called
        // from a child class, the child already received this event via the
        // parent::registerModelEvent call above, so we'll skip for that.
        if (static::class !== self::class) {
            return;
        }

        // Don't forward events to children models during the parent's boot
        // lifecycle, as the children will also be booting and will also
        // register these events themselves via their boot lifecycle.
        if (self::parentIsBooting()) {
            return;
        }

        $childTypes = (new static)->getChildTypes();

        foreach ($childTypes as $childClass) {
            if ($childClass !== self::class) {
                $childClass::registerModelEvent($event, $callback);
            }
        }
    }

    /**
     * Determine if the parent model is currently in its boot lifecycle.
     *
     * This checks whether bootIfNotBooted is in the call stack, which covers
     * the entire boot lifecycle including boot(), booted(), and whenBooted() callbacks.
     */
    protected static function parentIsBooting(): bool
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            if (($trace['function'] ?? '') === 'bootIfNotBooted') {
                return true;
            }
        }

        return false;
    }

    /**
     * Eager-loads the relations on single-table inheritance models.
     */
    public function scopeChildrenWith(EloquentBuilder $query, array $relations): void
    {
        EagerLoadingException::throwOnUnsupportedLaravelVersions();

        $query->afterQuery(fn ($models) => $models->loadChildren($relations));
    }

    /**
     * Eager-loads the relations counts on single-table inheritance models.
     */
    public function scopeChildrenWithCount(EloquentBuilder $query, array $relations): void
    {
        EagerLoadingException::throwOnUnsupportedLaravelVersions();

        $query->afterQuery(fn ($models) => $models->loadChildrenCount($relations));
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
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $ownerKey
     * @param  string|null  $relation
     * @return BelongsTo<TRelatedModel, $this>
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null): BelongsTo
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey) && $instance->hasParent) {
            $foreignKey = Str::snake($instance->getClassNameForRelationships()) . '_' . $instance->getKeyName();
        }

        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return HasMany<TRelatedModel, $this>
     */
    public function hasMany($related, $foreignKey = null, $localKey = null): HasMany
    {
        return parent::hasMany($related, $foreignKey, $localKey);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  string|class-string<Model>|null  $table
     * @param  string|null  $foreignPivotKey
     * @param  string|null  $relatedPivotKey
     * @param  string|null  $parentKey
     * @param  string|null  $relatedKey
     * @param  string|null  $relation
     * @return BelongsToMany<TRelatedModel, $this>
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
            $table = $this->joiningTable($instance->getClassNameForRelationships());
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

    public function getClassNameForRelationships(): string
    {
        return class_basename($this);
    }

    public function getInheritanceColumn(): string
    {
        return property_exists($this, 'childColumn') ? $this->childColumn : 'type';
    }

    /**
     * @param  mixed  $aliasOrClass
     */
    public function classFromAlias($aliasOrClass): string
    {
        $childTypes = $this->getChildTypes();

        // Handling Enum casting for `type` column
        if ($aliasOrClass instanceof UnitEnum) {
            $aliasOrClass = $aliasOrClass->value;
        }

        if (isset($childTypes[$aliasOrClass])) {
            return $childTypes[$aliasOrClass];
        }

        return $aliasOrClass;
    }

    public function classToAlias(string $className): mixed
    {
        $childTypes = $this->getChildTypes();

        if (in_array($className, $childTypes)) {
            return array_search($className, $childTypes);
        }

        return $className;
    }

    public function getChildTypes(): array
    {
        if (method_exists($this, 'childTypes')) {
            return $this->childTypes();
        }

        if (property_exists($this, 'childTypes')) {
            return $this->childTypes;
        }

        return [];
    }

    /**
     * Convert the current model instance into another child type.
     *
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return new<T>
     */
    public function become(string $class): object
    {
        return tap(new $class($attributes = $this->getAttributes()), function ($instance) use ($class, $attributes) {
            $instance->setRawAttributes(array_merge($attributes, [
                $this->getInheritanceColumn() => $this->classToAlias($class),
            ]));

            $instance->exists = true;

            $instance->setConnection($this->getConnectionName());

            $instance->setRelations($this->relations);

            $instance->fireModelEvent('becoming', false);
        });
    }

    /**
     * Eager load relationships on single-table inheritance models.
     */
    public function loadChildren(array $relations): static
    {
        EagerLoadingException::throwOnUnsupportedLaravelVersions();

        $this->load($relations[get_class($this)] ?? []);

        return $this;
    }

    /**
     * Eager load relationship counts on single-table inheritance models.
     */
    public function loadChildrenCount(array $relations): static
    {
        EagerLoadingException::throwOnUnsupportedLaravelVersions();

        $this->loadCount($relations[get_class($this)] ?? []);

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getChildModel(array $attributes)
    {
        $className = $this->classFromAlias(
            $attributes[$this->getInheritanceColumn()]
        );

        return new $className((array) $attributes);
    }
}

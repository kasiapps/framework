<?php

namespace Kasi\Database\Eloquent;

use Kasi\Contracts\Container\BindingResolutionException;
use Kasi\Contracts\Foundation\Application;
use Kasi\Database\Eloquent\Relations\Relation;
use Kasi\Support\Collection as BaseCollection;
use Kasi\Support\Facades\Gate;
use Kasi\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use SplFileObject;

use function Kasi\Support\enum_value;

class ModelInspector
{
    /**
     * The Kasi application instance.
     *
     * @var \Kasi\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The methods that can be called in a model to indicate a relation.
     *
     * @var array<int, string>
     */
    protected $relationMethods = [
        'hasMany',
        'hasManyThrough',
        'hasOneThrough',
        'belongsToMany',
        'hasOne',
        'belongsTo',
        'morphOne',
        'morphTo',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];

    /**
     * Create a new model inspector instance.
     *
     * @param  \Kasi\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Extract model details for the given model.
     *
     * @param  class-string<\Kasi\Database\Eloquent\Model>|string  $model
     * @param  string|null  $connection
     * @return array{"class": class-string<\Kasi\Database\Eloquent\Model>, database: string, table: string, policy: class-string|null, attributes: \Kasi\Support\Collection, relations: \Kasi\Support\Collection, events: \Kasi\Support\Collection, observers: \Kasi\Support\Collection, collection: class-string<\Kasi\Database\Eloquent\Collection<\Kasi\Database\Eloquent\Model>>, builder: class-string<\Kasi\Database\Eloquent\Builder<\Kasi\Database\Eloquent\Model>>}
     *
     * @throws BindingResolutionException
     */
    public function inspect($model, $connection = null)
    {
        $class = $this->qualifyModel($model);

        /** @var \Kasi\Database\Eloquent\Model $model */
        $model = $this->app->make($class);

        if ($connection !== null) {
            $model->setConnection($connection);
        }

        return [
            'class' => get_class($model),
            'database' => $model->getConnection()->getName(),
            'table' => $model->getConnection()->getTablePrefix().$model->getTable(),
            'policy' => $this->getPolicy($model),
            'attributes' => $this->getAttributes($model),
            'relations' => $this->getRelations($model),
            'events' => $this->getEvents($model),
            'observers' => $this->getObservers($model),
            'collection' => $this->getCollectedBy($model),
            'builder' => $this->getBuilder($model),
        ];
    }

    /**
     * Get the column attributes for the given model.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return \Kasi\Support\Collection<int, array<string, mixed>>
     */
    protected function getAttributes($model)
    {
        $connection = $model->getConnection();
        $schema = $connection->getSchemaBuilder();
        $table = $model->getTable();
        $columns = $schema->getColumns($table);
        $indexes = $schema->getIndexes($table);

        return (new BaseCollection($columns))
            ->map(fn ($column) => [
                'name' => $column['name'],
                'type' => $column['type'],
                'increments' => $column['auto_increment'],
                'nullable' => $column['nullable'],
                'default' => $this->getColumnDefault($column, $model),
                'unique' => $this->columnIsUnique($column['name'], $indexes),
                'fillable' => $model->isFillable($column['name']),
                'hidden' => $this->attributeIsHidden($column['name'], $model),
                'appended' => null,
                'cast' => $this->getCastType($column['name'], $model),
            ])
            ->merge($this->getVirtualAttributes($model, $columns));
    }

    /**
     * Get the virtual (non-column) attributes for the given model.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @param  array  $columns
     * @return \Kasi\Support\Collection
     */
    protected function getVirtualAttributes($model, $columns)
    {
        $class = new ReflectionClass($model);

        return (new BaseCollection($class->getMethods()))
            ->reject(
                fn (ReflectionMethod $method) => $method->isStatic()
                    || $method->isAbstract()
                    || $method->getDeclaringClass()->getName() === Model::class
            )
            ->mapWithKeys(function (ReflectionMethod $method) use ($model) {
                if (preg_match('/^get(.+)Attribute$/', $method->getName(), $matches) === 1) {
                    return [Str::snake($matches[1]) => 'accessor'];
                } elseif ($model->hasAttributeMutator($method->getName())) {
                    return [Str::snake($method->getName()) => 'attribute'];
                } else {
                    return [];
                }
            })
            ->reject(fn ($cast, $name) => (new BaseCollection($columns))->contains('name', $name))
            ->map(fn ($cast, $name) => [
                'name' => $name,
                'type' => null,
                'increments' => false,
                'nullable' => null,
                'default' => null,
                'unique' => null,
                'fillable' => $model->isFillable($name),
                'hidden' => $this->attributeIsHidden($name, $model),
                'appended' => $model->hasAppended($name),
                'cast' => $cast,
            ])
            ->values();
    }

    /**
     * Get the relations from the given model.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return \Kasi\Support\Collection
     */
    protected function getRelations($model)
    {
        return (new BaseCollection(get_class_methods($model)))
            ->map(fn ($method) => new ReflectionMethod($model, $method))
            ->reject(
                fn (ReflectionMethod $method) => $method->isStatic()
                    || $method->isAbstract()
                    || $method->getDeclaringClass()->getName() === Model::class
                    || $method->getNumberOfParameters() > 0
            )
            ->filter(function (ReflectionMethod $method) {
                if ($method->getReturnType() instanceof ReflectionNamedType
                    && is_subclass_of($method->getReturnType()->getName(), Relation::class)) {
                    return true;
                }

                $file = new SplFileObject($method->getFileName());
                $file->seek($method->getStartLine() - 1);
                $code = '';
                while ($file->key() < $method->getEndLine()) {
                    $code .= trim($file->current());
                    $file->next();
                }

                return (new BaseCollection($this->relationMethods))
                    ->contains(fn ($relationMethod) => str_contains($code, '$this->'.$relationMethod.'('));
            })
            ->map(function (ReflectionMethod $method) use ($model) {
                $relation = $method->invoke($model);

                if (! $relation instanceof Relation) {
                    return null;
                }

                return [
                    'name' => $method->getName(),
                    'type' => Str::afterLast(get_class($relation), '\\'),
                    'related' => get_class($relation->getRelated()),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Get the first policy associated with this model.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return string|null
     */
    protected function getPolicy($model)
    {
        $policy = Gate::getPolicyFor($model::class);

        return $policy ? $policy::class : null;
    }

    /**
     * Get the events that the model dispatches.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return \Kasi\Support\Collection
     */
    protected function getEvents($model)
    {
        return (new BaseCollection($model->dispatchesEvents()))
            ->map(fn (string $class, string $event) => [
                'event' => $event,
                'class' => $class,
            ])->values();
    }

    /**
     * Get the observers watching this model.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return \Kasi\Support\Collection
     *
     * @throws BindingResolutionException
     */
    protected function getObservers($model)
    {
        $listeners = $this->app->make('events')->getRawListeners();

        // Get the Eloquent observers for this model...
        $listeners = array_filter($listeners, function ($v, $key) use ($model) {
            return Str::startsWith($key, 'eloquent.') && Str::endsWith($key, $model::class);
        }, ARRAY_FILTER_USE_BOTH);

        // Format listeners Eloquent verb => Observer methods...
        $extractVerb = function ($key) {
            preg_match('/eloquent.([a-zA-Z]+)\: /', $key, $matches);

            return $matches[1] ?? '?';
        };

        $formatted = [];

        foreach ($listeners as $key => $observerMethods) {
            $formatted[] = [
                'event' => $extractVerb($key),
                'observer' => array_map(fn ($obs) => is_string($obs) ? $obs : 'Closure', $observerMethods),
            ];
        }

        return new BaseCollection($formatted);
    }

    /**
     * Get the collection class being used by the model.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return class-string<\Kasi\Database\Eloquent\Collection>
     */
    protected function getCollectedBy($model)
    {
        return $model->newCollection()::class;
    }

    /**
     * Get the builder class being used by the model.
     *
     * @template TModel of \Kasi\Database\Eloquent\Model
     *
     * @param  TModel  $model
     * @return class-string<\Kasi\Database\Eloquent\Builder<TModel>>
     */
    protected function getBuilder($model)
    {
        return $model->newQuery()::class;
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
     * @return class-string<\Kasi\Database\Eloquent\Model>
     *
     * @see \Kasi\Console\GeneratorCommand
     */
    protected function qualifyModel(string $model)
    {
        if (str_contains($model, '\\') && class_exists($model)) {
            return $model;
        }

        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->app->getNamespace();

        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
            ? $rootNamespace.'Models\\'.$model
            : $rootNamespace.$model;
    }

    /**
     * Get the cast type for the given column.
     *
     * @param  string  $column
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return string|null
     */
    protected function getCastType($column, $model)
    {
        if ($model->hasGetMutator($column) || $model->hasSetMutator($column)) {
            return 'accessor';
        }

        if ($model->hasAttributeMutator($column)) {
            return 'attribute';
        }

        return $this->getCastsWithDates($model)->get($column) ?? null;
    }

    /**
     * Get the model casts, including any date casts.
     *
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return \Kasi\Support\Collection
     */
    protected function getCastsWithDates($model)
    {
        return (new BaseCollection($model->getDates()))
            ->filter()
            ->flip()
            ->map(fn () => 'datetime')
            ->merge($model->getCasts());
    }

    /**
     * Determine if the given attribute is hidden.
     *
     * @param  string  $attribute
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function attributeIsHidden($attribute, $model)
    {
        if (count($model->getHidden()) > 0) {
            return in_array($attribute, $model->getHidden());
        }

        if (count($model->getVisible()) > 0) {
            return ! in_array($attribute, $model->getVisible());
        }

        return false;
    }

    /**
     * Get the default value for the given column.
     *
     * @param  array<string, mixed>  $column
     * @param  \Kasi\Database\Eloquent\Model  $model
     * @return mixed|null
     */
    protected function getColumnDefault($column, $model)
    {
        $attributeDefault = $model->getAttributes()[$column['name']] ?? null;

        return enum_value($attributeDefault) ?? $column['default'];
    }

    /**
     * Determine if the given attribute is unique.
     *
     * @param  string  $column
     * @param  array  $indexes
     * @return bool
     */
    protected function columnIsUnique($column, $indexes)
    {
        return (new BaseCollection($indexes))->contains(
            fn ($index) => count($index['columns']) === 1 && $index['columns'][0] === $column && $index['unique']
        );
    }
}

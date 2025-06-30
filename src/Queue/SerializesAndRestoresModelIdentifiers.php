<?php

namespace Kasi\Queue;

use Kasi\Contracts\Database\ModelIdentifier;
use Kasi\Contracts\Queue\QueueableCollection;
use Kasi\Contracts\Queue\QueueableEntity;
use Kasi\Database\Eloquent\Collection as EloquentCollection;
use Kasi\Database\Eloquent\Relations\Concerns\AsPivot;
use Kasi\Database\Eloquent\Relations\Pivot;
use Kasi\Support\Collection;

trait SerializesAndRestoresModelIdentifiers
{
    /**
     * Get the property value prepared for serialization.
     *
     * @param  mixed  $value
     * @param  bool  $withRelations
     * @return mixed
     */
    protected function getSerializedPropertyValue($value, $withRelations = true)
    {
        if ($value instanceof QueueableCollection) {
            return (new ModelIdentifier(
                $value->getQueueableClass(),
                $value->getQueueableIds(),
                $withRelations ? $value->getQueueableRelations() : [],
                $value->getQueueableConnection()
            ))->useCollectionClass(
                ($collectionClass = get_class($value)) !== EloquentCollection::class
                    ? $collectionClass
                    : null
            );
        }

        if ($value instanceof QueueableEntity) {
            return new ModelIdentifier(
                get_class($value),
                $value->getQueueableId(),
                $withRelations ? $value->getQueueableRelations() : [],
                $value->getQueueableConnection()
            );
        }

        return $value;
    }

    /**
     * Get the restored property value after deserialization.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function getRestoredPropertyValue($value)
    {
        if (! $value instanceof ModelIdentifier) {
            return $value;
        }

        return is_array($value->id)
                ? $this->restoreCollection($value)
                : $this->restoreModel($value);
    }

    /**
     * Restore a queueable collection instance.
     *
     * @param  \Kasi\Contracts\Database\ModelIdentifier  $value
     * @return \Kasi\Database\Eloquent\Collection
     */
    protected function restoreCollection($value)
    {
        if (! $value->class || count($value->id) === 0) {
            return ! is_null($value->collectionClass ?? null)
                ? new $value->collectionClass
                : new EloquentCollection;
        }

        $collection = $this->getQueryForModelRestoration(
            (new $value->class)->setConnection($value->connection), $value->id
        )->useWritePdo()->get();

        if (is_a($value->class, Pivot::class, true) ||
            in_array(AsPivot::class, class_uses($value->class))) {
            return $collection;
        }

        $collection = $collection->keyBy->getKey();

        $collectionClass = get_class($collection);

        return new $collectionClass(
            (new Collection($value->id))
                ->map(fn ($id) => $collection[$id] ?? null)
                ->filter()
        );
    }

    /**
     * Restore the model from the model identifier instance.
     *
     * @param  \Kasi\Contracts\Database\ModelIdentifier  $value
     * @return \Kasi\Database\Eloquent\Model
     */
    public function restoreModel($value)
    {
        return $this->getQueryForModelRestoration(
            (new $value->class)->setConnection($value->connection), $value->id
        )->useWritePdo()->firstOrFail()->load($value->relations ?? []);
    }

    /**
     * Get the query for model restoration.
     *
     * @template TModel of \Kasi\Database\Eloquent\Model
     *
     * @param  TModel  $model
     * @param  array|int  $ids
     * @return \Kasi\Database\Eloquent\Builder<TModel>
     */
    protected function getQueryForModelRestoration($model, $ids)
    {
        return $model->newQueryForRestoration($ids);
    }
}

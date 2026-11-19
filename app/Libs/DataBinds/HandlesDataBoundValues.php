<?php

namespace App\Libs\DataBinds;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Helper\DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait HandlesDataBoundValues
{
    /**
     * Wether to retrieve the default value as a single
     * attribute or as a collection from the database.
     *
     * @var bool
     */
    protected $manyRelation = false;

    /**
     * Get an instance of DataBinder.
     *
     * @return DataBinder
     */
    private function getDataBinder(): DataBinder
    {
        return app(DataBinder::class);
    }

    /**
     * Get the latest bound target.
     *
     * @return mixed
     */
    private function getBoundTarget()
    {
        return $this->getDataBinder()->get();
    }

    /**
     * Get an item from the latest bound target.
     *
     * @param mixed $bind
     * @param string $name
     * @return mixed
     */
    private function getBoundValue($bind, string $name)
    {
        if ($bind === false) {
            return null;
        }

        $bind = $bind ?: $this->getBoundTarget();
        $boundValue = data_get($bind, $name);
        $scopeName = 'scope'.ucfirst($name);

        if(!$boundValue && method_exists($this->model,  $scopeName)) {
            return $this->getByMethod($bind, $name);
        }

        if($boundValue instanceof EloquentCollection) {
            $this->manyRelation = true;
        }

        if ($this->manyRelation) {
            return $this->getAttachedKeysFromRelation($bind, $name);
        }

        if ($bind instanceof Model && $boundValue instanceof DateTimeInterface) {
            return $this->formatDateTime($bind, $name, $boundValue);
        }

        return $boundValue;
    }

    /**
     * Formats a DateTimeInterface if the key is specified as a date or datetime in the model.
     *
     * @param Model $model
     * @param string $key
     * @param DateTimeInterface $date
     * @return void
     */
    private function formatDateTime(Model $model, string $key, DateTimeInterface $date, $format = 'd.m.Y')
    {
        $cast = $model->getCasts()[$key] ?? null;

        if (!$cast || $cast === 'date' || $cast === 'datetime') {
            return Carbon::instance($date)->toJSON();
        }

        if ($this->isCustomDateTimeCast($cast)) {
            return $date->format(explode(':', $cast, 2)[1]);
        }

        return $date->format($format);
    }

    /**
     * Determine if the cast type is a custom date time cast.
     *
     * @param  string  $cast
     * @return bool
     */
    protected function isCustomDateTimeCast($cast)
    {
        return Str::startsWith($cast, [
            'date:',
            'datetime:',
            'immutable_date:',
            'immutable_datetime:',
        ]);
    }

    private function getByMethod($bind, $name)
    {
        $fn = $this->count ? 'count' : 'all';
        return $bind->{$name}($this->value)->$fn();
    }

    /**
     * Returns an array with the attached keys.
     *
     * @param mixed $bind
     * @param string $name
     * @return void
     */
    private function getAttachedKeysFromRelation($bind, string $name): array|int|null
    {
        if (!$bind instanceof Model) {
            return data_get($bind, $name);
        }

        $relation = $bind->{$name}();
        $fn = $this->count ? 'count' : 'all';

        if ($relation instanceof BelongsToMany) {
            $relatedKeyName = $relation->getRelatedKeyName();
            return $relation->getBaseQuery()
                ->get($relation->getRelated()->qualifyColumn($relatedKeyName))
                ->pluck($relatedKeyName)
                ->$fn();
        }

        if ($relation instanceof MorphMany) {
            $parentKeyName = $relation->getLocalKeyName();

            return $relation->getBaseQuery()
                ->get($relation->getQuery()->qualifyColumn($parentKeyName))
                ->pluck($parentKeyName)
                ->$fn();
        }

        return data_get($bind, $name);
    }
}

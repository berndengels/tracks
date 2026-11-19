<?php
namespace App\Traits\Models\Events;

use Event;

trait FireEvents
{
    public static function boot()
    {

        parent::boot();
        $prefix = lcfirst(class_basename(self::class));

        static::created(
            function ($model) use ($prefix) {
                Event::dispatch($prefix.'.created', $model);
            }
        );
        static::updated(
            function ($model) use ($prefix) {
                Event::dispatch($prefix.'.updated', $model);
            }
        );
        static::deleted(
            function ($model) use ($prefix) {
                Event::dispatch($prefix.'.deleted', $model);
            }
        );
        static::saved(
            function ($model) use ($prefix) {
                Event::dispatch($prefix.'.deleted', $model);
            }
        );
    }
}

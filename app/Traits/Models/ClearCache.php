<?php

namespace App\Traits\Models;

use Illuminate\Support\Facades\Cache;

trait ClearCache
{
    public static function bootClearCache()
    {
        self::created(function () {
            static::clearCache();
        });

        self::updated(function () {
            static::clearCache();
        });

        self::deleted(function () {
            static::clearCache();
        });
    }

    protected static function clearCache() {
        if(isset(static::$cacheKeys) && count(static::$cacheKeys) > 0) {
            foreach (static::$cacheKeys as $key) {
                Cache::forget($key);
            }
        }
    }
}

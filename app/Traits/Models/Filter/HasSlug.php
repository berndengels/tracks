<?php
namespace App\Traits\Models\Filter;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug()
    {
        static::creating(
            function ($table) {
                self::setSlug($table);
            }
        );
        static::updating(
            function ($table) {
                self::setSlug($table);
            }
        );
        static::saving(
            function ($table) {
                self::setSlug($table);
            }
        );
    }

    private static function setSlug($table)
    {
        if(!$table->slug) {
            $table->slug = Str::slug($table->title);
        }
    }
}

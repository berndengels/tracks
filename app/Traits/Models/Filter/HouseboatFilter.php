<?php

namespace App\Traits\Models\Filter;

use Illuminate\Database\Eloquent\Builder;

trait HouseboatFilter
{
    public function scopeHouseboat(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->whereId($id);
        }
        return $builder;
    }

    public function scopeHouseboatByDates(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->whereHouseboatId($id);
        }
        return $builder;
    }
}

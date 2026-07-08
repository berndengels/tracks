<?php

namespace App\Traits\Models\Filter;

use Illuminate\Database\Eloquent\Builder;

trait BoatFilter
{
    public function scopeBoat(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->find($id);
        }
        return $builder;
    }

    public function scopeBoatById(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->whereBoatId($id);
        }
        return $builder;
    }
}

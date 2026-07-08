<?php

namespace App\Traits\Models\Filter;

use Illuminate\Database\Eloquent\Builder;

trait GuestBoatFilter
{
    public function scopeGuestBoat(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->find($id);
        }
        return $builder;
    }

    public function scopeGuestBoatById(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->whereGuestBoatId($id);
        }
        return $builder;
    }
}

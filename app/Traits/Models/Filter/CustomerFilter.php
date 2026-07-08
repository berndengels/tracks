<?php

namespace App\Traits\Models\Filter;

use Illuminate\Database\Eloquent\Builder;

trait CustomerFilter
{
    public function scopeCustomer(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->whereId($id);
        }
        return $builder;
    }

    public function scopeCustomerByDates(Builder $builder, int $id = null) : Builder
    {
        if($id) {
            $builder->whereCustomerId($id);
        }
        return $builder;
    }
}

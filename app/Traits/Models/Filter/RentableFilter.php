<?php

namespace App\Traits\Models\Filter;

use Illuminate\Database\Eloquent\Builder;

trait RentableFilter
{
    public function scopeRelation(
        Builder $query,
        string|array $rentableType,
        int $rentableId = null
    ) : Builder
    {
        $query->whereHasMorph('rentable', $rentableType);

        if($rentableId) {
            $query->whereRentableId($rentableId);
        }
        return $query;
    }
}

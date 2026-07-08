<?php

namespace App\Traits\Models\Filter;

trait HasYearMonthOptions
{
    public static function monthOptions(
        string $groupKeyBy = 'month',
        string $prepend = 'Monat wählen',
        string|array $morphRelation = null
    )
    {
        $query = (static::class)::selectRaw("MONTH(`from`) AS `$groupKeyBy`, MONTHNAME(`from`) AS monthname");
        if($morphRelation) {
            $query->whereHasMorph('rentable', $morphRelation);
        }
        $options = $query->groupByRaw($groupKeyBy)
            ->get()
            ->keyBy($groupKeyBy)
            ->map
            ->monthname
        ;

        if($prepend) {
            $options->prepend($prepend, '');
        }

        return $options;
    }

    public static function yearOptions(
        string $groupKeyBy = 'year',
        $prepend = 'Jahr wählen',
        string|array $morphRelation = null
    )
    {
        $query = (static::class)::selectRaw("YEAR(`from`) AS `$groupKeyBy`");
        if($morphRelation) {
            $query->whereHasMorph('rentable', $morphRelation);
        }
        $options = $query->groupByRaw($groupKeyBy)
            ->get()
            ->keyBy($groupKeyBy)
            ->map
            ->year
        ;

        if($prepend) {
            $options->prepend($prepend, '');
        }

        return $options;
    }
}

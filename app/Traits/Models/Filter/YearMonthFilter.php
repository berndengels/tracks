<?php
namespace App\Traits\Models\Filter;

use Illuminate\Database\Eloquent\Builder;

trait YearMonthFilter
{
    public function scopeFromYearMonth(Builder $builder, string $year = null, string $month = null) : Builder
    {
        if($year) {
            $builder->whereYear('from', $year);
            if($month) {
                $builder->whereMonth('from', $month);
            }
        }
        return $builder;
    }

    public static function getMonthsByYears(string|array $morphRelation = null, $from = null, $until = null)
    {
        /**
         * @var $query Builder
         */
        $query = $morphRelation
            ? (static::class)::whereHasMorph('rentable', $morphRelation)
            : (static::class)::query();

        $query->selectRaw("DISTINCT MONTH(`from`) month, DATE_FORMAT(`from`, '%M') monthname, YEAR(`from`) year");

        if($from) {
            $query->whereDate('from', '>=', $from);
        }

        if($until) {
            $query->whereDate('until', '<=', $until);
        }

        return $query
            ->orderBy('year')
            ->orderBy('month');
    }

    public static function getMonthsByYearsOptions(string|array $morphRelation = null, $from = null, $until = null)
    {
        $data = self::getMonthsByYears(morphRelation: $morphRelation, from: $from, until: $until)->get();
        if($data) {
            $result = [];
            foreach($data as $date) {
                $result[$date->year][] = [
                    'month'     => $date->month,
                    'monthname' => $date->monthname,
                ];
            }
            return $result;
        }
        return null;
    }
}

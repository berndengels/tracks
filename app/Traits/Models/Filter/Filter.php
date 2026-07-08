<?php
namespace App\Traits\Models\Filter;

use Illuminate\Database\Eloquent\Builder;

/**
 *
 */
trait Filter
{
    /**
     * @param Builder $builder
     * @param string|null $name
     * @param $value
     * @return Builder
     */
    public function scopeLikeFilter(Builder $builder, string $name = null, $value = null) : Builder
    {
        if($value) {
            $builder->where($name, 'like', '%'.$value.'%');
        }
        return $builder;
    }

    /**
     * @param Builder $builder
     * @param string|null $name
     * @param $value
     * @return Builder
     */
    public function scopeFilter(Builder $builder, string $name = null, $value = null) : Builder
    {
        if($value) {
            $builder->where($name, '=', $value);
        }
        return $builder;
    }

    /**
     * @param Builder $builder
     * @param string|null $name
     * @param $value
     * @return Builder
     */
    public function scopeFilterMonth(Builder $builder, string $name = null, $value = null) : Builder
    {
        if($value) {
            $builder->whereRaw("MONTH($name) = ?", [$value]);
        }
        return $builder;
    }

    /**
     * @param Builder $builder
     * @param string|null $name
     * @param $value
     * @return Builder
     */
    public function scopeFilterYear(Builder $builder, string $name = null, $value = null) : Builder
    {
        if($value) {
            $builder->whereRaw("YEAR($name) = ?", [$value]);
        }
        return $builder;
    }

    /**
     * @param Builder $builder
     * @param string|null $name
     * @param $value
     * @return Builder
     */
    public function scopeFilterDateFrom(Builder $builder, string $name = null, $value = null) : Builder
    {
        if($value) {
            $builder->whereDate($name, '>=', $value);
        }
        return $builder;
    }

    /**
     * @param Builder $builder
     * @param string|null $name
     * @param $value
     * @return Builder
     */
    public function scopeFilterDateUntil(Builder $builder, string $name = null, $value = null) : Builder
    {
        if($value) {
            $builder->whereDate($name, '<=', $value);
        }
        return $builder;
    }
}

<?php

namespace App\Models;

use Dunn\GpxReader\DTO\Point;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string|null $desc
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoutePlannerData> $routePlannerData
 * @property-read int|null $route_planner_data_count
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoutePlanner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RoutePlanner extends Model
{
    use HasFactory;

    protected $table = 'route_planners';
    protected $guarded = ['id'];
    public $timestamps = true;

    public function routePlannerData()
    {
        return $this->hasMany(RoutePlannerData::class,'route_planner_id', 'id');
    }
}

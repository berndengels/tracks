<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Malhal\Geographical\Geographical;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

/**
 * @property int $id
 * @property int $route_planner_id
 * @property string $name
 * @property string|null $desc
 * @property string $lat
 * @property string $lng
 * @property \MatanYadaev\EloquentSpatial\Objects\Geometry $pos
 * @property-read \App\Models\RoutePlanner|null $routePlanner
 * @method static Builder|RoutePlannerData distance(float $latitude, float $longitude, $options = [])
 * @method static Builder|RoutePlannerData geofence(float $latitude, float $longitude, float $inner_radius, float $outer_radius)
 * @method static Builder|RoutePlannerData newModelQuery()
 * @method static Builder|RoutePlannerData newQuery()
 * @method static Builder|RoutePlannerData orderByDistance(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $direction = 'asc')
 * @method static Builder|RoutePlannerData orderByDistanceSphere(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $direction = 'asc')
 * @method static Builder|RoutePlannerData query()
 * @method static Builder|RoutePlannerData whereContains(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereCrosses(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereDesc($value)
 * @method static Builder|RoutePlannerData whereDisjoint(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereDistance(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $operator, int|float $value)
 * @method static Builder|RoutePlannerData whereDistanceSphere(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $operator, int|float $value)
 * @method static Builder|RoutePlannerData whereEquals(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereId($value)
 * @method static Builder|RoutePlannerData whereIntersects(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereLat($value)
 * @method static Builder|RoutePlannerData whereLng($value)
 * @method static Builder|RoutePlannerData whereName($value)
 * @method static Builder|RoutePlannerData whereNotContains(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereNotWithin(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereOverlaps(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData wherePos($value)
 * @method static Builder|RoutePlannerData whereRoutePlannerId($value)
 * @method static Builder|RoutePlannerData whereSrid(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, string $operator, int|float $value)
 * @method static Builder|RoutePlannerData whereTouches(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData whereWithin(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static Builder|RoutePlannerData withCentroid(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, string $alias = 'centroid')
 * @method static Builder|RoutePlannerData withDistance(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $alias = 'distance')
 * @method static Builder|RoutePlannerData withDistanceSphere(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $alias = 'distance')
 * @mixin \Eloquent
 */
class RoutePlannerData extends Model
{
    use HasFactory, Geographical, HasSpatial;

    const LATITUDE  = 'lat';
    const LONGITUDE = 'lng';

    protected $table = 'route_planner_data';
    protected static $kilometers = true;
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = [
        'lat'   => 'float',
        'lng'   => 'float',
        'pos'   => Point::class,
    ];

    public function routePlanner()
    {
        return $this->belongsTo(RoutePlanner::class,'route_planner_id', 'id');
    }
}

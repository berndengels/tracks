<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Malhal\Geographical\Geographical;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $track_id
 * @property string $lat
 * @property string $lon
 * @property string|null $datetime
 * @property string|null $speed
 * @property-read \App\Models\Track|null $track
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData query()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereTrackId($value)
 * @property string $lng
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData distance(float $latitude, float $longitude, $options = [])
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData geofence(float $latitude, float $longitude, float $inner_radius, float $outer_radius)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereLng($value)
 * @property \MatanYadaev\EloquentSpatial\Objects\Geometry $pos
 * @property int|null $stamp
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData orderByDistance(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData orderByDistanceSphere(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereContains(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereCrosses(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereDisjoint(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereDistance(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $operator, int|float $value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereDistanceSphere(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $operator, int|float $value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereEquals(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereIntersects(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereNotContains(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereNotWithin(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereOverlaps(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData wherePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereSrid(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, string $operator, int|float $value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereStamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereTouches(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData whereWithin(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData withCentroid(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, string $alias = 'centroid')
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData withDistance(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $alias = 'distance')
 * @method static \Illuminate\Database\Eloquent\Builder|TrackData withDistanceSphere(\Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $column, \Illuminate\Contracts\Database\Query\Expression|\MatanYadaev\EloquentSpatial\Objects\Geometry|string $geometryOrColumn, string $alias = 'distance')
 * @mixin \Eloquent
 */
class TrackData extends Model
{
    use HasFactory, Geographical, HasSpatial;

    const LATITUDE  = 'lat';
    const LONGITUDE = 'lng';

    protected static $kilometers = true;
    protected $table = 'track_data';
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = [
        'datetime'  => 'datetime',
        'lat'   => 'decimal:6',
        'lng'   => 'decimal:6',
        'km'   => 'decimal:1',
        'nm'   => 'decimal:1',
        'pos'   => Point::class,
        'stamp'    => 'int',
        'speed' => 'decimal:1'
    ];

    public function nmSpeed(): Attribute {
        return Attribute::make(get: fn () => round($this->speed * 1.944, 1));
    }

    public function kmSpeed(): Attribute {
        return Attribute::make(get: fn () => round($this->speed * 3.6, 1));
    }

    public function track()
    {
        return $this->belongsTo(Track::class,'track_id', 'id');
    }
}

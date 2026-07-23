<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Malhal\Geographical\Geographical;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

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
 * @mixin \Eloquent
 */
class TrackData extends Model
{
    use HasFactory, Geographical, HasSpatial;

    const LATITUDE  = 'lat';
    const LONGITUDE = 'lng';

    protected $table = 'track_data';
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = [
        'datetime'  => 'datetime',
        'lat'   => 'decimal:6',
        'lng'   => 'decimal:6',
        'pos'   => Point::class,
    ];
    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 * @mixin \Eloquent
 */
class TrackData extends Model
{
    use HasFactory;

    protected $table = 'track_data';
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = [
        'datetime'  => 'datetime',
        'lat'   => 'decimal:6',
        'lng'   => 'decimal:6'
    ];
    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}

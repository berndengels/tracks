<?php

namespace App\Models;

use App\Traits\Models\UseBooleanIcon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $name
 * @property string|null $year
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TrackData> $data
 * @property-read int|null $data_count
 * @method static \Database\Factories\TrackFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Track newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Track newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Track query()
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereYear($value)
 * @property \Illuminate\Support\Carbon $start
 * @property \Illuminate\Support\Carbon $end
 * @property bool $active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TrackData> $trackdata
 * @property-read int|null $trackdata_count
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Track whereStart($value)
 * @mixin \Eloquent
 */
class Track extends Model
{
    use HasFactory, UseBooleanIcon;

    protected $table = 'tracks';
    protected $guarded = ['id'];
    public $timestamps = true;
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'active'    => 'bool'
    ];

    public function trackdata()
    {
        return $this->hasMany(TrackData::class)->orderBy('datetime');
    }
}

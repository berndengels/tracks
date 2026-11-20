<?php

namespace App\Repositories;

use App\Models\Media;
use App\Models\Track;
use App\Models\TrackData;
use Illuminate\Database\Eloquent\Builder;

class GeoJSON
{
    public static function getlineFeatures($modulo, $year)
    {
        return Track::with(['trackdata'])
            ->whereActive(true)
            ->whereRaw('YEAR(start) = ?', [$year])
            ->orderBy('start')
            ->get()
            ->map(fn(Track $t) => [
                'type'  => 'Feature',
                'properties' => [
                    'id'    => $t->id,
                    'name'  => $t->name,
                    'start' => $t->start->addHours(2)->format('d.m.Y H:i'),
                    'end'   => $t->end->addHours(2)->format('d.m.Y H:i'),
                    'color' => 'red',
                ],
                'geometry' => [
                    'type'  => 'LineString',
                    'coordinates'   => $t->trackdata()
                        ->whereRaw('MOD(stamp, ?) = 0', [$modulo])
                        ->orderBy('datetime')
                        ->get()
                        ->map(fn (TrackData $p) => [(float) $p->lng,(float) $p->lat])
                        ->values()
                        ->toArray(),
                ]
            ]);
    }

    public static function getPointFeatures($modulo, $year)
    {
        return TrackData::with(['track'])
            ->whereRaw('MOD(`stamp`, ?) = 0', [$modulo])
            ->whereHas('track', fn(Builder $q) => $q->whereActive(true)->whereRaw('YEAR(`start`) = ?', [$year]))
            ->orderBy('datetime')
            ->get()
            ->map(fn (TrackData $p, $idx) => [
                'type'  => 'Feature',
                'properties' => [
                    'track' => [
                        'id'    => $p->track->id,
                        'name'  => $p->track->name,
                        'start' => $p->track->start->addHours(2)->format('H:i'),
                        'end' => $p->track->end->addHours(2)->format('H:i'),
                        'km'    => $p->track->km,
                        'nm'    => $p->track->nm,
                    ],
                    'id'        => $p->id,
                    // speed
                    'datetime'  => $p->datetime->addHours(2),
                ],
                'geometry'  => [
                    'type'  => 'Point',
                    'coordinates'  => [(float) $p->lng,(float) $p->lat],
                ],
            ])
            ->values()
            ->toArray();
    }

    public static function getMediaFeatures($year)
    {
        return Media::orderBy('created_at')
            ->whereRaw('YEAR(created_at) = ?', [$year])
            ->get()
            ->map(fn(Media $m) => [
                'type'  => 'Feature',
                'properties' => [
                    'id'  => $m->id,
                    'name'  => $m->name,
                    'filename' => $m->filename,
                    'type'  => $m->type,
                    'created'   => $m->created,
                ],
                'geometry'  => [
                    'type'  => 'Point',
                    'coordinates'  => [(float) $m->lat, (float) $m->lng],
                ],
            ])->toArray();
    }

    public static function getMediaFeaturesFromBound(array $southWest, array $northEast, int $year)
    {
        return Media::orderBy('created_at')
            ->whereRaw('YEAR(created_at) = ?', [$year])
            ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
            ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
            ->get()
            ->map(fn(Media $m) => [
                'type'  => 'Feature',
                'properties' => [
                    'id'        => $m->id,
                    'name'      => $m->name,
                    'file'      => $m->file,
                    'type'      => $m->type,
                    'created'   => $m->created,
                    'featureType'   => 'media',
                ],
                'geometry'  => [
                    'type'  => 'Point',
                    'coordinates'  => [(float) $m->lat, (float) $m->lng],
                ],
            ])->toArray();
    }

    public static function getlineFeaturesFromBound(array $southWest, array $northEast, int $modulo, int $year)
    {
        return Track::with(['trackdata'])
            ->whereActive(true)
            ->whereRaw('YEAR(`start`) = ?', [$year])
            ->whereHas('trackdata', fn(Builder $q) => $q
                ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
                ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
            )
            ->orderBy('start')
            ->get()
            ->map(fn(Track $t) => [
                'type'  => 'Feature',
                'properties' => [
                    'id'    => $t->id,
                    'name'  => $t->name,
                    'start' => $t->start->format('d.m.Y H:i'),
                    'end'   => $t->end->format('d.m.Y H:i'),
                    'km'    => $t->km,
                    'nm'    => $t->nm,
                    'points' => $t->trackdata()
                        ->whereRaw('stamp % ? = 0', [$modulo])
                        ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
                        ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
                        ->orderBy('datetime')
                        ->get()
                        ->map(fn (TrackData $p) => [
                            'name'  => $t->name,
                            'nmSpeed' => $p->nmSpeed,
                            'kmSpeed' => $p->kmSpeed,
                            'time' => $p->datetime->format('H:i')
                        ])
                        ->values()
                        ->toArray()
                ],
                'geometry' => [
                    'type'  => 'LineString',
                    'coordinates'   => $t->trackdata()
                        ->whereRaw('stamp % ? = 0', [$modulo])
                        ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
                        ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
                        ->orderBy('datetime')
                        ->get()
                        ->map(fn (TrackData $p, $idx) => [(float) $p->lng, (float) $p->lat])
                        ->values()
                        ->toArray(),
                ]
            ])
            ->values()
            ->toArray();
    }

    public static function getPointFeaturesFromBound(array $southWest, array $northEast, int $modulo, int $year)
    {
        $data = TrackData::with(['track'])
            ->whereRaw('stamp % ? = 0', [$modulo])
            ->whereHas('track', fn(Builder $q) => $q->whereActive(true)->whereRaw('YEAR(`start`) = ?', [$year]))
            ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
            ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
            ->orderBy('datetime')
            ->get()
            ->map(fn (TrackData $p) => [
                'type'  => 'Feature',
                'properties' => [
                    'track' => [
                        'id'    => $p->id,
                        'track_id'    => $p->track_id,
                        'name'  => $p->track->name,
                        'start' => $p->track->start->format('H:i'),
                        'end'   => $p->track->end->format('H:i'),
                        'nmSpeed' => $p->nmSpeed,
                        'knmSpeed' => $p->kmSpeed,
                        'km'    => $p->track->km,
                        'nm'    => $p->track->nm,
                    ],
                    'speed'     => $p->speed,
                    'datetime'  => $p->datetime->addHours(2),
                ],
                'geometry'  => [
                    'type'  => 'Point',
                    'coordinates'  => [(float) $p->lng,(float) $p->lat],
                ],
            ])
            ->values()
            ->toArray();

        return $data;
    }

    public static function getBounds($year)
    {
        $minLat = (float) TrackData::selectRaw('MIN(lat) AS val')->whereRaw('YEAR(datetime) = ?', [$year])->first()->val;
        $maxLat = (float) TrackData::selectRaw('MAX(lat) AS val')->whereRaw('YEAR(datetime) = ?', [$year])->first()->val;
        $minLng = (float) TrackData::selectRaw('MIN(lng) AS val')->whereRaw('YEAR(datetime) = ?', [$year])->first()->val;
        $maxLng = (float) TrackData::selectRaw('MAX(lng) AS val')->whereRaw('YEAR(datetime) = ?', [$year])->first()->val;

        $nordEast   = [$maxLat, $maxLng];
        $southWest  = [$minLat, $minLng];

        return collect([$nordEast, $southWest]);
    }

    public static function getNordEast($year)
    {
        return collect(self::getBounds($year)[0]);
    }

    public static function getSouthWest($year)
    {
        return collect(self::getBounds($year)[1]);
    }
}

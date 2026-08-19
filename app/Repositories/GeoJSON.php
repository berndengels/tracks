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
            ->whereRaw("YEAR(start) = $year")
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
                    'coordinates'   => $t->trackdata()->orderBy('datetime')->get()->map(function (TrackData $p, $idx) use ($modulo) {
                        if(0 === $idx || 0 === $idx % $modulo) {
                            return [(float) $p->lng,(float) $p->lat];
                        } else {
                            return null;
                        }
                    })->reject(fn($d) => !$d)->values()->toArray(),
                ]
            ]);
    }

    public static function getPointFeatures($modulo, $year)
    {
        return TrackData::with(['track'])
            ->whereHas('track', fn(Builder $q) => $q->whereActive(true)->whereRaw("YEAR(start) = $year"))
            ->orderBy('datetime')
            ->get()
            ->map(function (TrackData $p, $idx) use ($modulo) {
                if(0 === $idx || 0 === $idx % $modulo) {
                    return [
                        'type'  => 'Feature',
                        'properties' => [
                            'track' => [
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
                    ];
                } else {
                    return null;
                }
            })->reject(fn($d) => !$d)->values()->toArray();
    }

    public static function getMediaFeatures($year)
    {
        return Media::orderBy('created_at')
            ->whereRaw("YEAR(created_at) = $year")
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

    public static function getlineFeaturesFromBound(array $southWest, array $northEast, $modulo, $year)
    {
        return Track::with(['trackdata'])
            ->whereActive(true)
            ->whereRaw("YEAR(start) = $year")
            ->whereHas('trackdata', fn(Builder $q) => $q
//                ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
//                ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
/*
                ->whereLat('>=', $southWest['lat'])
                ->whereLat('<=', $northEast['lat'])
                ->whereLng('>=', $southWest['lng'])
                ->whereLng('<=', $northEast['lng'])
*/
            )
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
                        ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
                        ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
/*
                        ->whereLat('>=', $southWest['lat'])
                        ->whereLat('<=', $northEast['lat'])
                        ->whereLng('>=', $southWest['lng'])
                        ->whereLng('<=', $northEast['lng'])
*/
                        ->orderBy('datetime')
                        ->get()
                        ->map(function (TrackData $p, $idx) use ($modulo) {
                            if(0 === $idx || 0 === $idx % $modulo) {
                                return [(float) $p->lng,(float) $p->lat];
                            } else {
                                return null;
                            }
                    })->reject(fn($d) => !$d)->values()->toArray(),
                ]
            ]);
    }

    public static function getPointFeaturesFromBound(array $southWest, array $northEast, $modulo, $year)
    {
        return TrackData::with(['track'])
            ->whereHas('track', fn(Builder $q) => $q->whereActive(true)->whereRaw("YEAR(start) = $year"))
//            ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
//            ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
            ->whereBetween('lat', [$southWest['lat'], $northEast['lat']])
            ->whereBetween('lng', [$southWest['lng'], $northEast['lng']])
/*
            ->whereLat('>=', $southWest['lat'])
            ->whereLat('<=', $northEast['lat'])
            ->whereLng('>=', $southWest['lng'])
            ->whereLng('<=', $northEast['lng'])
*/
            ->orderBy('datetime')
            ->get()
            ->map(function (TrackData $p, $idx) use ($modulo) {
                if(0 === $idx || 0 === $idx % $modulo) {
                    return [
                        'type'  => 'Feature',
                        'properties' => [
                            'track' => [
                                'name'  => $p->track->name,
                                'start' => $p->track->start->addHours(2)->format('H:i'),
                                'end' => $p->track->end->addHours(2)->format('H:i'),
                            ],
                            'speed'    => $p->speed,
                            'datetime'  => $p->datetime->addHours(2)->format('d.m.Y H:i:s'),
                        ],
                        'geometry'  => [
                            'type'  => 'Point',
                            'coordinates'  => [(float) $p->lng,(float) $p->lat],
                        ],
                    ];
                } else {
                    return null;
                }
            })->reject(fn($d) => !$d)->values()->toArray();
    }

    public static function getBounds($year)
    {
        $minLat = (float) TrackData::selectRaw('MIN(lat) AS val')->whereRaw("YEAR(datetime) = $year")->first()->val;
        $maxLat = (float) TrackData::selectRaw('MAX(lat) AS val')->whereRaw("YEAR(datetime) = $year")->first()->val;
        $minLng = (float) TrackData::selectRaw('MIN(lng) AS val')->whereRaw("YEAR(datetime) = $year")->first()->val;
        $maxLng = (float) TrackData::selectRaw('MAX(lng) AS val')->whereRaw("YEAR(datetime) = $year")->first()->val;

        $nordEast   = [$maxLat, $maxLng];
        $southWest  = [$minLat, $minLng];

        return [$nordEast, $southWest];
    }
}

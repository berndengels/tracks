<?php

namespace App\Repositories;

use App\Models\Track;
use App\Models\TrackData;
use Illuminate\Database\Eloquent\Builder;

class GeoJSON
{
    public static function getlineFeatures($modulo)
    {
        return Track::with(['trackdata'])
            ->whereActive(true)
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

    public static function getPointFeatures($modulo)
    {
        return TrackData::with(['track'])
            ->whereHas('track', fn(Builder $q) => $q->whereActive(true))
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
                            'id'        => $p->id,
                            'speed'     => $p->speed,
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

    public static function getlineFeaturesFromTrack(Track $track, $modulo)
    {
        return Track::with(['trackdata'])
            ->whereActive(true)
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

    public static function getPointFeaturesFromTrack(Track $track, $modulo)
    {   $track->load(['track ']);

        return $track
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

    public static function getBounds()
    {
        $minLat = (float) TrackData::selectRaw('MIN(lat) AS val')->first()->val;
        $maxLat = (float) TrackData::selectRaw('MAX(lat) AS val')->first()->val;
        $minLng = (float) TrackData::selectRaw('MIN(lng) AS val')->first()->val;
        $maxLng = (float) TrackData::selectRaw('MAX(lng) AS val')->first()->val;

        $nordEast   = [$maxLat, $maxLng];
        $southWest  = [$minLat, $minLng];

        return [$nordEast, $southWest];
    }
}

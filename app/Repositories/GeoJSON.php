<?php

namespace App\Repositories;

use App\Models\Media;
use App\Models\RoutePlanner;
use App\Models\RoutePlannerData;
use App\Models\Track;
use App\Models\TrackData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GeoJSON
{
    public static function getRoutePlannerlineFeatures($year)
    {
        return RoutePlanner::with(['routePlannerData'])
            ->whereRaw('YEAR(created_at) = ?', [$year])
            ->orderBy('created_at')
            ->get()
            ->map(fn(RoutePlanner $t) => [
                'type'  => 'Feature',
                'properties' => [
                    'id'    => $t->id,
                    'name'  => $t->name,
                    'desc'  => $t->desc,
                    'points' => $t->routePlannerData()
                        ->orderBy('id')
                        ->get()
                        ->map(fn (RoutePlannerData $p) => [
                            'latlng'    => [
                                'lat'   => $p->lat,
                                'lng'   => $p->lng,
                            ],
                            'id'  => $p->id,
                            'name'  => $p->name,
                            'desc'  => $p->desc,
                            'route_planner_id'  => $p->route_planner_id,
                        ])
				],
                'geometry' => [
                    'type'  => 'LineString',
                    'coordinates'   => $t->routePlannerData()
                        ->orderBy('id')
                        ->get()
                        ->map(fn (RoutePlannerData $p) => [(float) $p->lng,(float) $p->lat])
//                        ->values()
//                        ->toArray(),
                ]
            ]);
    }

    public static function getlineFeaturesByRoutePlanner(RoutePlanner $routePlaner)
    {
        $routePlaner->load(['routePlannerData']);

        return [
            'type'  => 'Feature',
            'properties' => [
                'id'    => $routePlaner->id,
                'name'  => $routePlaner->name,
                'desc'  => $routePlaner->desc,
                'points' => $routePlaner->routePlannerData()
                    ->orderBy('id')
                    ->get()
                    ->map(fn (RoutePlannerData $p) => [
                        'latlng'    => [
                            'lat'   => $p->lat,
                            'lng'   => $p->lng,
                        ],
                        'id'  => $p->id,
                        'name'  => $p->name,
                        'desc'  => $p->desc,
                        'route_planner_id'  => $p->route_planner_id,
                    ])

            ],
            'geometry' => [
                'type'  => 'LineString',
                'coordinates'   => $routePlaner->routePlannerData()
                    ->orderBy('id')
                    ->get()
                    ->map(fn (RoutePlannerData $p) => [(float) $p->lng,(float) $p->lat])
            ]
        ];
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
//            ->values()
//            ->toArray()
			;
    }

    public static function getMediaFeatures($year)
    {
        return Media::orderBy('created_at')
            ->whereActive(true)
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
            ])
//			->toArray()
			;
    }

    public static function getMediaFeaturesFromBound(array $southWest, array $northEast, int $year)
    {
        return Media::orderBy('created_at')
            ->whereActive(true)
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
            ])
//			->toArray()
			;
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
                            'latlng'    => [
                                'lat'   => $p->lat,
                                'lng'   => $p->lng,
                            ],
                            'name'  => $t->name,
                            'track_id'    => $t->id,
                            'nmSpeed' => $p->nmSpeed,
                            'kmSpeed' => $p->kmSpeed,
                            'time' => $p->datetime->format('H:i')
                        ])
//                        ->toArray()
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
//                        ->values()
//                        ->toArray()
                    ,
                ]
            ])
//            ->values()
//            ->toArray()
            ;
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
//            ->values()
//            ->toArray()
		;

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

    public static function getBoundsByModel(Model $model, $relation = null)
    {
        if($relation && $model->isRelation($relation)) {
            $minLat = (float) app($model::class)->{$relation}()->selectRaw('MIN(lat) AS val')->first()->val;
            $maxLat = (float) app($model::class)->{$relation}()->selectRaw('MAX(lat) AS val')->first()->val;
            $minLng = (float) app($model::class)->{$relation}()->selectRaw('MIN(lng) AS val')->first()->val;
            $maxLng = (float) app($model::class)->{$relation}()->selectRaw('MAX(lng) AS val')->first()->val;

            $nordEast   = [$maxLat, $maxLng];
            $southWest  = [$minLat, $minLng];

            return collect([$nordEast, $southWest]);
        } else {
            $minLat = (float) app($model::class)::selectRaw('MIN(lat) AS val')->first()->val;
            $maxLat = (float) app($model::class)::selectRaw('MAX(lat) AS val')->first()->val;
            $minLng = (float) app($model::class)::selectRaw('MIN(lng) AS val')->first()->val;
            $maxLng = (float) app($model::class)::selectRaw('MAX(lng) AS val')->first()->val;

            $nordEast   = [$maxLat, $maxLng];
            $southWest  = [$minLat, $minLng];

            return collect([$nordEast, $southWest]);
        }
    }

    public static function getNordEast($year)
    {
        return collect(self::getBounds($year)[0]);
    }

    public static function getSouthWest($year)
    {
        return collect(self::getBounds($year)[1]);
    }

    public static function getNordEastByModel(Model $model, $relation = null)
    {
        return collect(self::getBoundsByModel($model, $relation)[0]);
    }

    public static function getSouthWestByModel(Model $model, $relation = null)
    {
        return collect(self::getBoundsByModel($model, $relation)[1]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackData;
use Illuminate\Support\Collection;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $modulo = 1)
    {
        $lineFeatures = Track::orderBy('start')
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
//                    'coordinates'   => $t->data()->orderBy('datetime')->get()->map(fn(TrackData $p) => [(float) $p->lng,(float) $p->lat]),
                    'coordinates'   => $t->data()->orderBy('datetime')->get()->map(function (TrackData $p, $idx) use ($modulo) {
                        if(0 === $idx || 0 === $idx % $modulo) {
                            return [(float) $p->lng,(float) $p->lat];
                        } else {
                            return null;
                        }
                    })->reject(fn($d) => !$d)->values()->toArray(),
                ]
        ]);

        $pointFeatures = TrackData::orderBy('datetime')
            ->get()
            ->map(function (TrackData $p, $idx) use ($modulo) {
                if(0 === $idx || 0 === $idx % $modulo) {
                    return [
                        'type'  => 'Feature',
                        'properties' => [
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
        $points = collect([
            'type'  => 'FeatureCollection',
            'name'  =>  'Bernds Segeltörn 2026',
            'features'  => $pointFeatures,
        ])->toJson();

        $tracks = collect([
            'type'  => 'FeatureCollection',
            'name'  =>  'Bernds Segeltörn 2026',
            'features'  => $lineFeatures,
        ])->toJson();

        $minLat = (float) TrackData::selectRaw('MIN(lat) AS val')->first()->val;
        $maxLat = (float) TrackData::selectRaw('MAX(lat) AS val')->first()->val;
        $minLng = (float) TrackData::selectRaw('MIN(lng) AS val')->first()->val;
        $maxLng = (float) TrackData::selectRaw('MAX(lng) AS val')->first()->val;

        $nordEast   = [$maxLat, $maxLng];
        $southWest  = [$minLat, $minLng];
        $bounds     = [$nordEast, $southWest];

//        \Storage::disk('public')->write('geo.json', $tracks);

        return view('tracks.index', compact('tracks','points','bounds'));
    }
}

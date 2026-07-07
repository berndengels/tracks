<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackData;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $features = Track::orderBy('start')
            ->get()
            ->map(fn(Track $t) => [
                'type'  => 'Feature',
                'properties' => [
                    'id'    => $t->id,
                    'name'  => $t->name,
                    'start' => $t->start->format('d.m.Y H:i'),
                    'end'   => $t->end->format('d.m.Y H:i'),
                    'color' => 'red',
                ],
                'geometry' => [
                    'type'  => 'LineString',
                    'coordinates'   => $t->data()->orderBy('datetime')->get()->map(fn(TrackData $p) => [
                        (float) $p->lng,
                        (float) $p->lat,
                        0,
                        $p->speed
                    ]),
                ]
        ]);

        $tracks = collect([
            'type'  => 'FeatureCollection',
            'name'  =>  'Bernds Segeltörn 2026',
            'features'  => $features,
        ])->toJson();

        $minLat = (float) TrackData::selectRaw('MIN(lat) AS val')->first()->val;
        $maxLat = (float) TrackData::selectRaw('MAX(lat) AS val')->first()->val;
        $minLng = (float) TrackData::selectRaw('MIN(lng) AS val')->first()->val;
        $maxLng = (float) TrackData::selectRaw('MAX(lng) AS val')->first()->val;

        $nordEast   = [$maxLat, $maxLng];
        $southWest  = [$minLat, $minLng];
        $bounds     = [$nordEast, $southWest];
//        \Storage::disk('public')->write('geo.json', $tracks);

        return view('tracks.index', compact('tracks','bounds'));
    }
}

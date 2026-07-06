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
        $tracks = Track::orderBy('start')
            ->get()
            ->map(fn(Track $t) => [
                'name'  => $t->name,
                'start' => $t->start->format('d.m.Y H:i'),
                'end'   => $t->end->format('d.m.Y H:i'),
                'points'    => $t->data()->orderBy('id')->get()->map(fn(TrackData $p) => [
                    'point' => [$p->lat, $p->lng],
                    'speed' => $p->speed,
                    'time'  => $p->datetime->format('d.m.Y H:i')
                ])->toArray()
        ]);

        $minLat = TrackData::selectRaw('MIN(lat) AS val')->first()->val;
        $maxLat = TrackData::selectRaw('MAX(lat) AS val')->first()->val;
        $minLng = TrackData::selectRaw('MIN(lng) AS val')->first()->val;
        $maxLng = TrackData::selectRaw('MAX(lng) AS val')->first()->val;
        $bounds = [[$minLat, $minLng], [$maxLat, $maxLng]];

        return view('tracks.index', compact('tracks','bounds'));
    }
}

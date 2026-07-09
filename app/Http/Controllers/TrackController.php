<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackData;
use App\Repositories\GeoJSON;
use Illuminate\Database\Eloquent\Builder;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $modulo = 10)
    {
        $lineFeatures = GeoJSON::getlineFeatures($modulo);
        $pointFeatures = GeoJSON::getPointFeatures($modulo);
        $bounds     = GeoJSON::getBounds();

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

//        \Storage::disk('public')->write('geo.json', $tracks);

        return view('tracks.index', compact('tracks','points','bounds'));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Track;
use App\Models\TrackData;
use Illuminate\Http\Request;
use App\Repositories\GeoJSON;
use App\Http\Controllers\Controller;

class ApiTrackController extends Controller
{
    private $limit = 10000;
    private $zoomModulo = [
        8 => 10000,
        9 => 1000,
        10 => 1000,
        11 => 1000,
        12 => 100,
        13 => 100,
        14 => 10,
        15 => 1,
        16 => 1,
        17 => 1,
        18 => 1,
        19 => 1,
        20 => 1,
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Track $track)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Track $track)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Track $track)
    {
        //
    }

    public function getData(Request $request, int $modulo = null) {
        $southWest = $request->post('southWest');
        $northEast = $request->post('northEast');
        $zoom = $request->post('zoom');

        if(! $modulo) {
            $modulo = $this->zoomModulo[$zoom] ?? $this->limit;
        }

        $lineFeatures = GeoJSON::getlineFeaturesFromBound( southWest: $southWest, northEast: $northEast, modulo: $modulo);
        $pointFeatures = GeoJSON::getPointFeaturesFromBound(southWest: $southWest, northEast: $northEast, modulo: $modulo);

        $points = collect([
            'type'  => 'FeatureCollection',
            'name'  =>  'Bernds Segeltörn 2026',
            'features'  => $pointFeatures,
        ]);

        $tracks = collect([
            'type'  => 'FeatureCollection',
            'name'  =>  'Bernds Segeltörn 2026',
            'features'  => $lineFeatures,
        ]);

        return response()->json([
            'tracks' => $tracks,
            'points'    => $points,
            'bounds'    => [$northEast, $southWest]
        ]);
    }
}

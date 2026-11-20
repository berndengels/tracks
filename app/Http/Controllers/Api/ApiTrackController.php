<?php

namespace App\Http\Controllers\Api;

use App\Models\Track;
use App\Models\TrackData;
use Illuminate\Http\Request;
use App\Repositories\GeoJSON;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\Controller as BaseController;

class ApiTrackController extends BaseController
{
    private $limit = 360;
    private $ttl = 3600 * 24 * 364;
    private $year;
    private $maxZoom = 16;
    private $zoomModulo = [
        8 => 60,
        9 => 60,
        10 => 30,
        11 => 30,
        12 => 3,
        13 => 3,
        14 => 3,
        15 => 3,
        16 => 1,
    ];

    public function __construct()
    {
        $this->useCache = config('tracks.use_cache');
        $this->year = Carbon::today(config('app.timezone'))->year;
    }

    public function index(Request $request, $year = null) {
        try {
            $southWest  = $request->post('southWest');
            $northEast  = $request->post('northEast');
            $zoom       = (int) $request->post('zoom');
            $year       = $request->post('year');
            $modulo     = $this->zoomModulo[$zoom] ?? $this->limit;

            if(! $year) {
                $year = $this->year;
            }

            $trackName  = "Bernds Segeltörn $year";

            if($this->useCache) {
                $lineFeatures   = Cache::remember("lineFeatures{$zoom}_{$year}", $this->ttl, fn() => GeoJSON::getlineFeaturesFromBound(southWest: $southWest, northEast: $northEast, modulo: $modulo, year: $year));
                $mediaFeatures  = Cache::remember("mediaFeatures{$zoom}_{$year}", $this->ttl, fn() => GeoJSON::getMediaFeaturesFromBound(southWest: $southWest, northEast: $northEast, year: $year));
            } else {
                $lineFeatures   = GeoJSON::getlineFeaturesFromBound(southWest: $southWest, northEast: $northEast, modulo: $modulo, year: $year);
                $mediaFeatures  = GeoJSON::getMediaFeaturesFromBound(southWest: $southWest, northEast: $northEast, year: $year);
            }

            $tracks = [
                'type'  => 'FeatureCollection',
                'name'  =>  $trackName,
                'features'  => $lineFeatures,
            ];

            $media = collect([
                'type'  => 'FeatureCollection',
                'name'  =>  $trackName,
                'features'  => $mediaFeatures,
            ]);

            return response()->json([
                'error'     => null,
                'tracks'    => $tracks,
                'media'     => $media,
                'bounds'    => [$southWest, $northEast],
                'maxZoom'   => $this->maxZoom,
            ]);

        } catch(\Exception $e) {
            $error = [
                'msg' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'code'  => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ];
            return response()->json(['error' => $error]);
        }
    }
}

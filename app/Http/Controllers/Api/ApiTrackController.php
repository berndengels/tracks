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

    public function index(Request $request) {
        try {
            $southWest  = $request->post('southWest');
            $northEast  = $request->post('northEast');
            $zoom       = (int) $request->post('zoom');
            $year       = $request->post('year', null);
            $modulo     = $this->zoomModulo[$zoom] ?? $this->limit;
			$routePlannerId  = $request->post('route-planner-id', 1);
			$speed		= $request->post('speed', 5.0);
			$startTime		= $request->post('speed', Carbon::now(config('app.timezone')));

            if(! $year) {
                return response()->json([
                    'error'     => 'Bitte ein Jahr auswählen',
                    'tracks'    => null,
                    'media'     => null,
					'planners'  => null,
                    'bounds'    => [$southWest, $northEast],
                    'maxZoom'   => $this->maxZoom,
                ]);
            }

            $trackName  = "Bernds Segeltörn $year";

            if($this->useCache) {
                $plannerLineFeatures   = Cache::remember("plannerLineFeatures{$zoom}_{$year}", $this->ttl, fn() => GeoJSON::getRoutePlannerlineFeatures($routePlannerId, $speed, $startTime));
                $lineFeatures   = Cache::remember("lineFeatures{$zoom}_{$year}", $this->ttl, fn() => GeoJSON::getlineFeaturesFromBound(southWest: $southWest, northEast: $northEast, modulo: $modulo, year: $year));
                $mediaFeatures  = Cache::remember("mediaFeatures{$zoom}_{$year}", $this->ttl, fn() => GeoJSON::getMediaFeaturesFromBound(southWest: $southWest, northEast: $northEast, year: $year));
            } else {
                $plannerLineFeatures   = GeoJSON::getRoutePlannerlineFeatures($routePlannerId, $speed, $startTime);
                $lineFeatures   = GeoJSON::getlineFeaturesFromBound(southWest: $southWest, northEast: $northEast, modulo: $modulo, year: $year);
                $mediaFeatures  = GeoJSON::getMediaFeaturesFromBound(southWest: $southWest, northEast: $northEast, year: $year);
            }

            $planners = [
                'type'  => 'FeatureCollection',
                'name'  =>  'TourPlanung',
                'features'  => $plannerLineFeatures,
            ];

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
                'planners'  => $planners,
                'bounds'    => [$southWest, $northEast],
                'maxZoom'   => $this->maxZoom,
            ]);

        } catch(\Exception $e) {
            $error = [
                'msg' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'code'  => $e->getCode(),
            ];

            return response()->json(['error' => $error]);
        }
    }
}

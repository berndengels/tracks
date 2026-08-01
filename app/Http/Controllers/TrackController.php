<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Repositories\GeoJSON;
use App\Repositories\Gis;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class TrackController extends Controller
{
    private $limit = 100;
    private $ttl = 3600 * 6;
    private $useCache = true;

    /**
     * Display a listing of the resource.
     */
    public function index(int $modulo = null)
    {
        $startTime = Carbon::now(config('app.timezone'));

        if(! $modulo) {
            $modulo = $this->limit;
        }

        $bounds = Cache::remember('bounds', $this->ttl, fn() => GeoJSON::getBounds());

        if($this->useCache) {
            $lineFeatures = Cache::remember('lineFeatures', $this->ttl, fn() => GeoJSON::getlineFeatures($modulo));
            $pointFeatures = Cache::remember('pointFeatures', $this->ttl, fn() => GeoJSON::getPointFeatures($modulo));
            $mediaFeatures = Cache::remember('mediaFeatures', $this->ttl, fn() => GeoJSON::getMediaFeatures());
        } else {
            $lineFeatures = GeoJSON::getlineFeatures($modulo);
            $pointFeatures = GeoJSON::getPointFeatures($modulo);
            $mediaFeatures = GeoJSON::getMediaFeatures();
        }

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

        $media = collect([
            'type'  => 'FeatureCollection',
            'name'  =>  'Bernds Segeltörn 2026',
            'features'  => $mediaFeatures,
        ])->toJson();

        $km = Track::select('km')->get('km')->pluck('km')->sum();
        $nm = Track::select('nm')->get('nm')->pluck('nm')->sum();

        $endTime = Carbon::now(config('app.timezone'));
        $duration = $endTime->diffInSeconds($startTime);

        return view('tracks.index', compact('tracks','points','bounds','duration','media','km','nm'));
//        return view('tracks.index', compact('northEast','southWest', 'bounds'));
    }
}

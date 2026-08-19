<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Repositories\GeoJSON;
use App\Repositories\Gis;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class TrackController extends Controller
{
    private $limit = 1000;
    private $ttl = 3600 * 6;
    private $useCache = true;
    private $year;

    public function __construct()
    {
        $this->year = Carbon::today(config('app.timezone'))->year;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(string $year = null)
    {
        $startTime = Carbon::now(config('app.timezone'));

        if(! $year) {
            $year = $this->year;
        } elseif ( $this->year !== $year) {
            Cache::clear();
        }

        $modulo = $this->limit;

        $bounds = Cache::remember('bounds', $this->ttl, fn() => GeoJSON::getBounds($year));

        if($this->useCache) {
            $lineFeatures = Cache::remember('lineFeatures', $this->ttl, fn() => GeoJSON::getlineFeatures($modulo, $year));
            $pointFeatures = Cache::remember('pointFeatures', $this->ttl, fn() => GeoJSON::getPointFeatures($modulo, $year));
            $mediaFeatures = Cache::remember('mediaFeatures', $this->ttl, fn() => GeoJSON::getMediaFeatures($year));
        } else {
            $lineFeatures = GeoJSON::getlineFeatures($modulo, $year);
            $pointFeatures = GeoJSON::getPointFeatures($modulo, $year);
            $mediaFeatures = GeoJSON::getMediaFeatures($year);
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

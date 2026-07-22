<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackData;
use App\Repositories\GeoJSON;
use App\Repositories\Gis;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Plutuss\Facades\MediaAnalyzer;

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
//        $bounds     = GeoJSON::getBounds();
//        $northEast = ['lat' => $bounds[0][0], 'lng' => $bounds[0][1]];
//        $southWest = ['lat' => $bounds[1][0], 'lng'  => $bounds[1][1]];
//        $bounds = json_encode($bounds);
//        $northEast = json_encode($northEast);
//        $southWest = json_encode($southWest);
        if($this->useCache) {
            $lineFeatures = Cache::remember('lineFeatures', $this->ttl, fn() => GeoJSON::getlineFeatures($modulo));
            $pointFeatures = Cache::remember('pointFeatures', $this->ttl, fn() => GeoJSON::getPointFeatures($modulo));
        } else {
            $lineFeatures = GeoJSON::getlineFeatures($modulo);
            $pointFeatures = GeoJSON::getPointFeatures($modulo);
            $mediaFeatures = GeoJSON::getMedia();
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

//        \Storage::disk('public')->write('geo.json', $tracks);
//        $firstPoint = TrackData::select('lat','lng')->orderBy('datetime')->first();
//        $distance = TrackData::select('lat','lng')->distance($firstPoint->lat, $firstPoint->lng)->get()->map->distance->sum();

        $endTime = Carbon::now(config('app.timezone'));
        $duration = $endTime->diffInSeconds($startTime);

        return view('tracks.index', compact('tracks','points','bounds','duration','media'));
//        return view('tracks.index', compact('northEast','southWest', 'bounds'));
    }
}

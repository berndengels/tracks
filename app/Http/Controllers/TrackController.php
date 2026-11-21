<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackData;
use App\Repositories\GeoJSON;
use App\Repositories\Gis;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class TrackController extends Controller
{
    private $ttl = 3600 * 24 * 364;
    private $maxZoom = 16;
    private $zoom = 8;
    private $useCache;
    private $year;

    public function __construct()
    {
        $this->useCache = config('tracks.use_cache');
        $this->year = Carbon::today(config('app.timezone'))->year;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(string $year = null)
    {
        if(! $year) {
            $year = $this->year;
        }

        $maxZoom = $this->maxZoom;
        $zoom = $this->zoom;

        if($this->useCache) {
            $southWest  = Cache::remember('southWest'.$year, $this->ttl, fn() => GeoJSON::getSouthWest($year)->toJson());
            $northEast  = Cache::remember('northEast'.$year, $this->ttl, fn() => GeoJSON::getNordEast($year)->toJson());
            $distances  = Cache::remember('distances'.$year, $this->ttl, fn() => Track::selectRaw('ROUND(SUM(km), 0) km, ROUND(SUM(nm), 0) nm')->whereRaw('YEAR(`start`) = ?', [$year])->first());
            $center     = Cache::remember('center'.$year, $this->ttl, fn() => TrackData::selectRaw('AVG(lat) lat, AVG(lng) lng')->first()->toJson());
            $yearOptions = Cache::remember('yearOptions', $this->ttl, fn() => Track::selectRaw('DISTINCT YEAR(`start`) year')->get()
                ->map(fn($y) => [
                    'value' => (string) $y->year,
                    'label' => (string) $y->year,
                ])
                ->prepend([
                    'value' => '',
                    'label' => '--bitte wählen--',
                ])
                ->toJson());
        } else {
            $southWest  = GeoJSON::getSouthWest($year)->toJson();
            $northEast  = GeoJSON::getNordEast($year)->toJson();
            $distances  = Track::selectRaw('ROUND(SUM(km), 0) km, ROUND(SUM(nm), 0) nm')->whereRaw('YEAR(`start`) = ?', [$year])->first();
            $center     = TrackData::selectRaw('AVG(lat) lat, AVG(lng) lng')->first()->toJson();
            $yearOptions = Track::selectRaw('DISTINCT YEAR(`start`) year')->get()
                ->map(fn($y) => [
                    'value' => (string) $y->year,
                    'label' => (string) $y->year,
                ])
                ->prepend([
                    'value' => '',
                    'label' => '--bitte wählen--',
                ])
                ->toJson();
        }

        $km = (int) $distances->km;
        $nm = (int) $distances->nm;

//        return view('tracks.index', compact('tracks','points','bounds','duration','media','km','nm'));
        return view('tracks.index', compact('northEast','southWest', 'km','nm','maxZoom','yearOptions','center','zoom'));
    }
}

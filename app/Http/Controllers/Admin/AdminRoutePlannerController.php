<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutePlannerRequest;
use App\Http\Requests\UpdateRoutePlannerRequest;
use App\Models\RoutePlanner;
use App\Models\RoutePlannerData;
use App\Repositories\GeoJSON;
use Dunn\GpxReader\Facades\Gpx;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use MatanYadaev\EloquentSpatial\Objects\Point;

class AdminRoutePlannerController extends Controller
{
    private $maxZoom = 16;
    private $zoom = 8;
    private $year;

    public function __construct()
    {
        $this->year = Carbon::today(config('app.timezone'))->year;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RoutePlanner::orderBy('created_at')->paginate($this->paginationLimit);
        return view('admin.route-planners.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoutePlannerRequest $request)
    {
        try {
            /**
             * @var $file UploadedFile
             */
            $file = $request->validated('tracks');
            setlocale(LC_CTYPE, 'de_DE');

            if($file) {
                $modulo = config('tracks.track.import.modulo');
                $content = trim($file->getContent());
                $gpx = Gpx::parseFromString($content);

//                dd($gpx);

                if($gpx) {
                    if('Navionics Boating App' === $gpx->creator) {
                        if($gpx->routes && count($gpx->routes) > 0) {
                            $route = $gpx->routes[0];
                            $routePlanner = RoutePlanner::create([
                                'name'  => $route->name,
                                'desc'  => $route->desc,
                            ]);
                            if($route->points && count($route->points) > 0) {
                                $points = collect($route->points)->map(fn($p) => [
                                    'route_planner_id'  => $routePlanner->id,
                                    'name'  => $p->name,
                                    'lat'   => $p->latitude,
                                    'lng'   => $p->longitude,
                                ])->toArray();
                                $routePlanner->routePlannerData()->insertOrIgnore($points);
                            }
                        }
                    } else {
                        $routePlanner = RoutePlanner::create([
                            'name'  => $gpx->metadata->name,
                            'desc'  => $gpx->metadata->desc,
                        ]);

                        if($routePlanner) {
                            foreach ($gpx->waypoints as $item) {
                                $lat = round($item->latitude, 6);
                                $lng = round($item->longitude, 6);

                                $data = [
                                    'route_planner_id'  => $routePlanner->id,
                                    'name'  => $item->name,
                                    'desc'  => $item->desc,
                                    'lat'   => $lat,
                                    'lng'   => $lng,
                                    'pos'   => new Point($lat, $lng),
                                ];
                                RoutePlannerData::create($data);
//                            $routePlanner->routePlannerData()->create($data);
                            }
                        }

                        Cache::clear();
                    }
                }
            }

            return redirect()->route('admin.route-planners.index')->with('success', 'Daten erfolgreich angelegt!');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoutePlanner $routePlanner)
    {
        $maxZoom = $this->maxZoom;
        $zoom = $this->zoom;
//        $southWest  = GeoJSON::getSouthWestByModel(model:  $routePlanner, relation: 'routePlannerData')->toJson();
        $southWest  = GeoJSON::getSouthWestByModel(model: $routePlanner, relation:  'routePlannerData')->toJson();
        $northEast  = GeoJSON::getNordEastByModel(model: $routePlanner, relation: 'routePlannerData')->toJson();
        $center     = RoutePlannerData::selectRaw('AVG(lat) lat, AVG(lng) lng')->first()->toJson();
        $yearOptions = RoutePlanner::selectRaw('DISTINCT YEAR(`created_at`) year')->get()
            ->map(fn($y) => [
                'value' => (string) $y->year,
                'label' => (string) $y->year,
            ])
            ->prepend([
                'value' => null,
                'label' => '--bitte wählen--',
            ])
            ->toJson();
        $routePlanner->load(['routePlannerData']);

        return view('admin.route-planners.edit', compact('routePlanner','northEast','southWest','maxZoom','yearOptions','center','zoom'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoutePlannerRequest $request, RoutePlanner $routePlanner)
    {
        $routePlanner->update($request->validated());
        Cache::clear();

        return redirect()->route('admin.route-planners.index')->with('success', 'Daten erfolgreich bearbeitet!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoutePlanner $routePlanner)
    {
        $routePlanner->routePlannerData()->truncate();
        $routePlanner->delete();
        Cache::clear();

        return redirect()->route('admin.route-planners.index')->with('success', 'Daten erfolgreich gelöscht!');
    }
}

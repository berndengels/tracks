<?php

namespace App\Http\Controllers\Api;

use App\Models\RoutePlanner;
use App\Repositories\GeoJSON;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutePlannerDataRequest;

class ApiRoutePlannerDataController extends Controller
{
    public function index(RoutePlanner $routePlanner)
    {
        $data = GeoJSON::getlineFeaturesByRoutePlanner($routePlanner);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoutePlannerDataRequest $request, RoutePlanner $routePlanner)
    {
        $routePlanner->routePlannerData()->truncate();

        $data = collect($request->validated('waypoints'))->map(fn($wp) => [
            'route_planner_id' => $wp['route_planner_id'],
            'lat'   => (float) $wp['lat'],
            'lng'   => (float) $wp['lng'],
        ])->toArray();

        $routePlanner->routePlannerData()->insertOrIgnore($data);

        $routePlanner->refresh();

        $plannerLineFeatures = GeoJSON::getRoutePlannerlineFeatures(year: $request->validated('year'));

        $planners = [
            'type'  => 'FeatureCollection',
            'name'  =>  'TourPlanung',
            'features'  => $plannerLineFeatures,
        ];

        return response()->json([
            'planners'  => $planners,
        ]);
    }
}

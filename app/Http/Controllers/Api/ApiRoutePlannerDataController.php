<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateRoutePlannerDataRequest;
use App\Http\Requests\UpdateRoutePlannerRequest;
use App\Models\RoutePlanner;
use App\Models\RoutePlannerData;
use App\Repositories\GeoJSON;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutePlannerDataRequest;
use Illuminate\Support\Carbon;

class ApiRoutePlannerDataController extends Controller
{
	public function __construct()
	{
		$this->useCache = config('tracks.use_cache');
		$this->year = Carbon::today(config('app.timezone'))->year;
	}

	public function index(RoutePlanner $routePlanner)
    {
        $data = GeoJSON::getlineFeaturesByRoutePlanner($routePlanner);

        return response()->json($data);
    }

	public function show(RoutePlannerData $routePlannerData) {

		return response()->json($routePlannerData->attributesToArray());
	}

	/**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoutePlannerDataRequest $request)
    {
        $data = collect($request->validated('waypoints'))->map(fn($wp) => [
            'route_planner_id' => $wp['route_planner_id'],
            'lat'   => (float) $wp['lat'],
            'lng'   => (float) $wp['lng'],
        ])->toArray();

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

	/**
	 * Store a newly created resource in storage.
	 */
	public function storePoints(StoreRoutePlannerDataRequest $request, RoutePlanner $routePlanner)
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

	public function update(UpdateRoutePlannerDataRequest $request, RoutePlannerData $routePlannerData) {
		$routePlannerData->update($request->validated());
		$routePlannerData->refresh();

		return response()->json($routePlannerData->attributesToArray());
	}

	public function destroy(RoutePlannerData $routePlannerData) {
		$id = $routePlannerData->id;
		$routePlannerData->delete();

		return response()->json([
			'id'	=> $id,
		]);
	}
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\RoutePlannerData;
use Illuminate\Http\Resources\Json\JsonResource;

class RoutePlannerResourseData extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
	 * @val $this RoutePlannerData
     */
    public function toArray(Request $request): array
    {
        return [
			'id'	=> $this->id,
			'route_planner_id'	=> $this->route_planner_id,
			'name'	=> $this->name,
			'desc'	=> $this->desc,
			'lat'	=> $this->lat,
			'lng'	=> $this->lng,
		];
    }
}

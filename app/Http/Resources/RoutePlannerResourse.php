<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\RoutePlanner;
use Illuminate\Http\Resources\Json\JsonResource;

class RoutePlannerResourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
	 * @val RoutePlanner $this
     */
    public function toArray(Request $request): array
    {
        return [
			'id'	=> $this->id,
			'name'	=> $this->name,
			'desc'	=> $this->desc,
			'date'	=> $this->created_at->format('d.m.Y'),
			'points' => RoutePlannerResourseData::collection($this->routePlannerData),
		];
    }
}

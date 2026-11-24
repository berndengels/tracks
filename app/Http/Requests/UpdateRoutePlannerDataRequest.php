<?php

namespace App\Http\Requests;

class UpdateRoutePlannerDataRequest extends AdminRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'route_planner_id'  => 'required',
            'name'  => 'required',
            'desc'  => '',
            'lat'  => 'required',
            'lng'  => 'required',
            'pos'  => 'required',
        ];
    }
}

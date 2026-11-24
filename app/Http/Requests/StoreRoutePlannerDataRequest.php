<?php

namespace App\Http\Requests;

class StoreRoutePlannerDataRequest extends AdminRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
//            'route_planner_id' => 'required',
            'waypoints'  => [],
            'year' => '',
        ];
    }
}

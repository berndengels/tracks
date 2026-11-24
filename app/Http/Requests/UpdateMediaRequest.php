<?php

namespace App\Http\Requests;

class UpdateMediaRequest extends AdminRequest
{
    protected $booleanFields = ['active'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'  => 'required',
            'track_id'  => '',
        ];
    }
}

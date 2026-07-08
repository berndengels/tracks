<?php

namespace App\Http\Requests;

class StoreTrackRequest extends AdminRequest
{
    protected $booleanFields = ['active'];
    protected $defaults = [
        'active'    => true,
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tracks'    => 'file',
            'active'    => 'boolean'
        ];
    }
}

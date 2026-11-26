<?php

namespace App\Http\Requests;

class UpdateTrackRequest extends AdminRequest
{
	public function authorize(): bool
	{
		return true;
	}

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
            'active'    => 'boolean'
        ];
    }
}

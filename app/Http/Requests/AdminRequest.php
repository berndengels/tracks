<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
    protected $decimalFields;
    protected $booleanFields;
    protected $defaults;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation()
    {
        // z.B Preise
        $this->handleDecimals();
        // z.B: Checkboxen, true/false Werte
        $this->handleBooleans();
        // Default Werte setzen
        $this->handleDefaults();
    }

    protected function failedValidation(Validator $validator)
    {
        if(request()->isXmlHttpRequest() && request()->wantsJson()) {
            $this->errors = $validator->errors();
            die(json_encode(['errors' => $this->errors]));
        }
        parent::failedValidation($validator);
    }

    private function handleDecimals()
    {
        // aus 2000,50 wird 20005.00
        if($this->decimalFields) {
            foreach($this->decimalFields as $field) {
                $decimal = trim(str_replace('€', '', $this->$field));
                $decimal = preg_replace("/^([^,]+)([\.,])([\d]{2})$/","$1.$3", $decimal);

                if(preg_match("/^([\d\.]+)\.([\d]{2})$/", $decimal)) {
                    $arr = explode(".", $decimal);
                    if(count($arr) > 2) {
                        $last = array_pop($arr);
                        $first = implode('', $arr);
                        $decimal = $first.'.'.$last;
                    }
                } else if (preg_match("/^([\d]+)([\.])([\d]+)$/", $decimal)) {
                    $decimal = str_replace('.', '', $decimal);
                }

                $this->merge([
                    $field	=> $decimal,
                ]);
            }
        }
    }

    private function handleBooleans()
    {
        if($this->booleanFields) {
            foreach($this->booleanFields as $field) {
                $this->merge([
                    $field	=> isset($this->$field) ? 1 : 0,
                ]);
            }
        }
    }

    private function handleDefaults()
    {
        if($this->defaults) {
            foreach ($this->defaults as $field => $value) {
                $this->merge([
                    $field	=> $value,
                ]);
            }
        }
    }
}

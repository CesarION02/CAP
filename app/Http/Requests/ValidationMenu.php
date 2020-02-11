<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidationUrl;

class ValidationMenu extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:50|unique:menu,name,' . $this->route('id'),
            'url' => ['required','max:100',new ValidationUrl],
            'icono' => 'required|max:50'
        ];
    }

    public function messages(){
        return [
            'name.required' => 'El campo nombre es requerido',
            'url.required' => 'El campo url es requerido',
        ];
    }
}

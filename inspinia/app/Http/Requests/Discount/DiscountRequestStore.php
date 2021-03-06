<?php

namespace App\Http\Requests\Discount;

use App\Http\Requests\Request;

class DiscountRequestStore extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        

        $rules = [
            'description' => 'required|max:100|unique:discounts',
        ];        
        
        if($this->request->get('temporary')){
             $rules['initial_date'] = 'required|date_format:d/m/Y';
             $rules['final_date'] = 'required|date_format:d/m/Y';
        }
        
        if ($this->request->get('type') == 'M'){        
            
            $rules['amount'] = 'required|numeric|min:0';        
        
        }elseif ($this->request->get('type') == 'P'){

            $rules['percent'] = 'required|numeric|min:0|max:100';                
        }
        
        return $rules;

    }

    public function messages()
    {
        return [
            'amount.required'  => 'El monto del cargo es obligatorio.',
            'description.unique'  => 'La descripción del descuento ya ha sido registrada.',
            'percent.required'  => 'El porcentaje del cargo es obligatorio.',
            'amount.numeric'  => 'El monto del cargo debe ser numérico.',
            'percent.numeric'  => 'El porcentaje del cargo debe ser numérico.',
            'initial_date.required'  => 'La fecha desde es obligatoria.',
            'initial_date.date'  => 'La fecha desde es inválida.',
            'final_date.required'  => 'La fecha hasta es obligatoria.',
            'final_date.date'  => 'La fecha hasta es inválida.'
        ];
    }

}
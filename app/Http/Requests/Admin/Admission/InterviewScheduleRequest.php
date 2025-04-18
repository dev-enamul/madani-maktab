<?php

namespace App\Http\Requests\Admin\Admission;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InterviewScheduleRequest extends FormRequest
{ 
     protected function failedValidation(Validator $validator)
     {
         throw new HttpResponseException(
             response()->json([
                 'message' => 'Validation failed',
                 'errors' => $validator->errors()
             ], 422)
         );
     }
     
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'candidate_id'      => ['required', 'exists:users,id'],  
            'date'              => ['required'],
            'time'              => ['nullable', 'date_format:H:i'],  
            'message'             => ['required', 'string', 'max:500'],  
        ];
    }
}

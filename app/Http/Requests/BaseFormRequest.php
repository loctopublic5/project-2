<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }

    protected function failedValidator(Validator $validate){
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation Error',
            'error' => $validate->errors()
        ], 422));
    } 
} 
?>
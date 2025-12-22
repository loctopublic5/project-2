<?php

namespace App\Http\Requests\System;

use App\Http\Requests\BaseFormRequest;

class UploadFileRequest extends BaseFormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 
                        'max:5120',                                             // Tối đa 5MB 
                        'mimes:jpeg,jpg,png,gif,webp,pdf,docx,xlsx',
            ],

            'target_type' => ['required', 'string',
                                function($attribute, $value, $fail){
                                    $allowedModels = [
                                        'App\Models\User',
                                        'App\Models\Product',
                                        'App\Models\DealerRequest',
                                    ];
                                    if(!in_array($value,$allowedModels)){
                                        return $fail("Loại đối tượng '$value' không được phép upload file.");
                                    }

                                    if(!class_exists($value)){
                                        return $fail("Đối tượng '$value' không tồn tại trong hệ thống.");
                                    }

                                    //Đảm bảo class này là một Model của Laravel
                                    if(!is_subclass_of($value, 'Illuminate\Database\Eloquent\Model')){
                                        return $fail("Đối tượng '$value' không phải là Model hợp lệ.");
                                    }
                                }
            ],

            'target_id' => ['required','integer',
                                function($attribute, $value, $fail){
                                    $targetType = $this->input('target_type');
                                    
                                    // Nếu target_type sai (không tồn tại class), thì rule ở trên đã bắt lỗi rồi.
                                    // Ta return luôn để tránh lỗi code ở dưới.
                                    if(!class_exists($targetType)){
                                        return;
                                    }

                                    $exists = $targetType::where('id', $value)->exists();

                                    // Logic tương đương: Select count(*) from [bảng_của_model] where id = $value
                                    if(!$exists){
                                        $fail("Không tìm thấy bản ghi có ID $value trong đối tượng $targetType.");
                                    }
                                }
            ],
        ];
    }
}

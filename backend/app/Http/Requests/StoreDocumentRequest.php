<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => ['required','exists:employees,id'],
            'file' => ['required','file','max:10240'], // 10MB
        ];
    }
}

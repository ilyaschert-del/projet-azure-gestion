<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190','unique:employees,email'],
            'position' => ['nullable','string','max:120'],
        ];
    }
}

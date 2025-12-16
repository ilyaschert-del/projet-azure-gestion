<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('employee')?->id ?? null;

        return [
            'name' => ['sometimes','required','string','max:120'],
            'email' => ['sometimes','required','email','max:190', Rule::unique('employees','email')->ignore($id)],
            'position' => ['nullable','string','max:120'],
        ];
    }
}

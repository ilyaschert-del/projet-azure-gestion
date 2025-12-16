<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => ['required','exists:employees,id'],
            'title' => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'due_at' => ['nullable','date'],
            'status' => ['nullable','in:pending,done,late'],
        ];
    }
}

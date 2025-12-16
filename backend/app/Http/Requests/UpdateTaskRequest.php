<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes','required','string','max:200'],
            'description' => ['nullable','string'],
            'due_at' => ['nullable','date'],
            'status' => ['nullable','in:pending,done,late'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReminderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'task_id' => ['required','exists:tasks,id'],
            'remind_at' => ['required','date'],
            'status' => ['nullable','in:scheduled,sent,cancelled'],
        ];
    }
}

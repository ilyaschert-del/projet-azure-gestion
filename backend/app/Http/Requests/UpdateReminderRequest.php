<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReminderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'remind_at' => ['sometimes','required','date'],
            'status' => ['nullable','in:scheduled,sent,cancelled'],
        ];
    }
}

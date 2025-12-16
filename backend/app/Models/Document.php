<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = ['employee_id', 'original_name', 'mime_type', 'size', 'blob_path'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

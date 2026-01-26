<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class ApiLog extends Model
{
    use Prunable;
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    public function prunable()
{
    return static::where('created_at', '<=', now()->subDays(14));
}
}

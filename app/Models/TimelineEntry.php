<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineEntry extends Model
{
    protected $fillable = [
        'type',
        'institution',
        'role',
        'start_year',
        'end_year',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_year' => 'integer',
            'end_year'   => 'integer',
            'sort_order' => 'integer',
        ];
    }
}

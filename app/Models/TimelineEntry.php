<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TimelineEntry extends Model
{
    use HasTranslations;

    public array $translatable = ['institution', 'role', 'description'];

    protected $fillable = [
        'type',
        'institution',
        'role',
        'start_year',
        'end_year',
        'description',
        'sort_order',
    ];
}

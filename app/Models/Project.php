<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Project extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'description',
        'tech_stack',
        'demo_url',
        'repo_url',
        'thumbnail_path',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tech_stack' => 'array',
            'is_published' => 'boolean',
        ];
    }
}

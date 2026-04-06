<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

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
            'tech_stack'   => 'array',
            'is_published' => 'boolean',
        ];
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->thumbnail_path
                ? asset('storage/' . ltrim($this->thumbnail_path, '/'))
                : null,
        );
    }

    protected $appends = ['thumbnail_url'];
}

<?php

namespace App\Services;

use App\Models\Project;
use App\Models\SiteSetting;

class MetaTagService
{
    /**
     * Generate meta tags array for the home page.
     */
    public function forHome(): array
    {
        $siteName = SiteSetting::get('hero_name', config('app.name'));
        $ogImage  = SiteSetting::get('og_default_image', '');

        return [
            'title'           => $siteName . ' — Portfolio',
            'description'     => 'Portfolio profesional ' . $siteName . '. Laravel, Vue.js, Inertia.js developer.',
            'ogTitle'         => $siteName . ' — Portfolio',
            'ogDescription'   => 'Portfolio profesional ' . $siteName . '. Laravel, Vue.js, Inertia.js developer.',
            'ogImage'         => $ogImage ? asset('storage/' . ltrim($ogImage, '/')) : '',
            'ogUrl'           => url('/'),
            'keywords'        => 'portfolio, laravel, vue, inertia, developer, ' . $siteName,
        ];
    }

    /**
     * Generate meta tags array for a specific project page.
     */
    public function forProject(Project $project): array
    {
        $siteName = SiteSetting::get('hero_name', config('app.name'));
        $ogImage  = SiteSetting::get('og_default_image', '');

        $title       = is_array($project->title)
            ? ($project->title['id'] ?? $project->title['en'] ?? '')
            : (string) $project->title;

        $description = is_array($project->description)
            ? ($project->description['id'] ?? $project->description['en'] ?? '')
            : (string) $project->description;

        // Strip HTML tags from description for meta
        $plainDescription = strip_tags($description);
        $plainDescription = mb_substr($plainDescription, 0, 160);

        $thumbnail = $project->thumbnail_path
            ? asset('storage/' . ltrim($project->thumbnail_path, '/'))
            : ($ogImage ? asset('storage/' . ltrim($ogImage, '/')) : '');

        $techStack = is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : '';

        return [
            'title'         => $title . ' — ' . $siteName,
            'description'   => $plainDescription,
            'ogTitle'       => $title . ' — ' . $siteName,
            'ogDescription' => $plainDescription,
            'ogImage'       => $thumbnail,
            'ogUrl'         => url('/') . '#portfolio',
            'keywords'      => 'portfolio, ' . $techStack . ', ' . $siteName,
        ];
    }
}

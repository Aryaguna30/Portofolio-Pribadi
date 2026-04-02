<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Skill;
use App\Models\SiteSetting;
use App\Models\TimelineEntry;
use App\Services\MetaTagService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class HomeController extends Controller
{
    public function __construct(private MetaTagService $metaTagService) {}

    public function index(): InertiaResponse
    {
        $projects = Project::where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $timelineEntries = TimelineEntry::orderBy('sort_order')->get();

        $skills = Skill::orderBy('sort_order')->get();

        $heroName        = SiteSetting::get('hero_name', '');
        $heroProfessions = SiteSetting::get('hero_professions', []);
        $heroDescription = SiteSetting::get('hero_description', '');
        $typingSpeed     = SiteSetting::get('typing_speed', 100);
        $cvFilePath      = SiteSetting::get('cv_file_path', null);
        $socialLinkedin  = SiteSetting::get('social_linkedin', '');
        $socialGithub    = SiteSetting::get('social_github', '');
        $socialEmail     = SiteSetting::get('social_email', '');

        // Ensure professions is always an array
        if (! is_array($heroProfessions)) {
            $heroProfessions = $heroProfessions ? [$heroProfessions] : [];
        }

        $cvUrl = $cvFilePath
            ? route('cv.download')
            : null;

        $meta = $this->metaTagService->forHome();

        return Inertia::render('Home', [
            'hero' => [
                'name'         => $heroName,
                'professions'  => $heroProfessions,
                'description'  => $heroDescription,
                'typingSpeed'  => (int) $typingSpeed,
            ],
            'projects'        => $projects,
            'timelineEntries' => $timelineEntries,
            'skills'          => $skills,
            'socialLinks'     => [
                'linkedin' => $socialLinkedin,
                'github'   => $socialGithub,
                'email'    => $socialEmail,
            ],
            'cvUrl' => $cvUrl,
            'meta'  => $meta,
        ]);
    }

    public function downloadCv(): Response
    {
        $path = SiteSetting::get('cv_file_path', null);

        if (empty($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path);
    }
}

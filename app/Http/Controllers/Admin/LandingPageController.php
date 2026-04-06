<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CvUploadRequest;
use App\Models\Skill;
use App\Models\SiteSetting;
use App\Models\TimelineEntry;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    public function __construct(private FileStorageService $fileStorage) {}

    /**
     * Display the landing page management form.
     *
     * Requirements: 10.1, 10.2, 10.5
     */
    public function index(): Response
    {
        return Inertia::render('Admin/LandingPage', [
            'hero' => [
                'name'        => SiteSetting::get('hero_name', ''),
                'professions' => SiteSetting::get('hero_professions', []),
                'description' => SiteSetting::get('hero_description', ''),
            ],
            'socialLinks' => [
                'linkedin' => SiteSetting::get('social_linkedin', ''),
                'github'   => SiteSetting::get('social_github', ''),
                'email'    => SiteSetting::get('social_email', ''),
            ],
            'timelineEntries' => TimelineEntry::orderBy('sort_order')->get(),
            'skills'          => Skill::orderBy('sort_order')->get(),
            'currentCvPath'   => SiteSetting::get('cv_file_path', null),
        ]);
    }

    /**
     * Update hero section settings.
     *
     * Requirements: 10.1, 10.2
     */
    public function updateHero(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'professions'   => ['required', 'array'],
            'professions.*' => ['string', 'max:255'],
            'description'   => ['nullable', 'string'],
        ]);

        SiteSetting::set('hero_name', $validated['name']);
        SiteSetting::set('hero_professions', $validated['professions']);
        SiteSetting::set('hero_description', $validated['description'] ?? '');

        return back()->with('success', 'Hero settings updated.');
    }

    /**
     * Upload a new CV file, deleting the old one if it exists.
     *
     * Requirements: 10.5, 10.6, 10.7, 10.9
     */
    public function uploadCv(CvUploadRequest $request): RedirectResponse
    {
        $oldPath = SiteSetting::get('cv_file_path', null);

        // Delete old CV if it exists
        if ($oldPath) {
            $this->fileStorage->deleteFile($oldPath);
        }

        // Store new CV
        $file        = $request->file('cv_file');
        $newPath     = 'cv/' . \Illuminate\Support\Str::uuid() . '.pdf';
        \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
            'cv',
            $file,
            basename($newPath)
        );

        SiteSetting::set('cv_file_path', $newPath);

        return back()->with('success', 'CV uploaded successfully.');
    }

    /**
     * Delete the current CV file.
     *
     * Requirements: 10.9
     */
    public function deleteCv(): RedirectResponse
    {
        $path = SiteSetting::get('cv_file_path', null);

        if ($path) {
            $this->fileStorage->deleteFile($path);
            SiteSetting::set('cv_file_path', null);
        }

        return back()->with('success', 'CV deleted.');
    }

    /**
     * Update social links settings.
     */
    public function updateSocialLinks(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'linkedin' => ['nullable', 'string', 'max:255'],
            'github'   => ['nullable', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255'],
        ]);

        SiteSetting::set('social_linkedin', $validated['linkedin'] ?? '');
        SiteSetting::set('social_github', $validated['github'] ?? '');
        SiteSetting::set('social_email', $validated['email'] ?? '');

        return back()->with('success', 'Social links updated.');
    }
}

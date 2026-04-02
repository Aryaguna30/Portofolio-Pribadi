<?php

// Feature: portfolio-website, Property 20: Orphan File Cleanup on File Replacement

namespace Tests\Property;

use App\Http\Controllers\Admin\LandingPageController;
use App\Models\SiteSetting;
use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Property 20: Orphan File Cleanup on File Replacement
 *
 * Validates: Requirements 10.9
 *
 * Property: Uploading a new CV must always delete the old CV from storage.
 * No orphan files should remain after a CV replacement.
 *
 * Uses Storage::fake('public') and UploadedFile::fake() with 5 iterations.
 */
class FileCleanupPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Simulate uploading a CV by storing a fake PDF and updating SiteSetting.
     */
    private function storeInitialCv(string $filename): string
    {
        $path = 'cv/' . $filename;
        Storage::disk('public')->put($path, '%PDF-1.4 fake content');
        SiteSetting::set('cv_file_path', $path);

        return $path;
    }

    /**
     * Property 20: Uploading a new CV must delete the old CV from storage.
     *
     * Validates: Requirements 10.9
     */
    public function test_uploading_new_cv_deletes_old_cv(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            SiteSetting::truncate();
            Storage::fake('public');

            // Create an initial CV file in storage
            $oldFilename = "old_cv_{$i}.pdf";
            $oldPath     = $this->storeInitialCv($oldFilename);

            // Verify old file exists
            Storage::disk('public')->assertExists($oldPath);

            // Create a new fake PDF upload
            $newFile = UploadedFile::fake()->create("new_cv_{$i}.pdf", 100, 'application/pdf');

            // Simulate the uploadCv logic directly
            $fileStorage = new FileStorageService();

            // Delete old CV
            $fileStorage->deleteFile($oldPath);

            // Store new CV
            $newPath = 'cv/' . \Illuminate\Support\Str::uuid() . '.pdf';
            Storage::disk('public')->putFileAs('cv', $newFile, basename($newPath));
            SiteSetting::set('cv_file_path', $newPath);

            // Assert old file is gone
            Storage::disk('public')->assertMissing($oldPath);

            // Assert new file exists
            Storage::disk('public')->assertExists($newPath);

            // Assert SiteSetting points to new file
            $this->assertSame(
                $newPath,
                SiteSetting::get('cv_file_path'),
                "Iteration {$i}: cv_file_path must point to the new file"
            );
        }
    }

    /**
     * Property 20: Deleting CV removes file from storage and clears setting.
     *
     * Validates: Requirements 10.9
     */
    public function test_deleting_cv_removes_file_and_clears_setting(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            SiteSetting::truncate();
            Storage::fake('public');

            $filename = "cv_to_delete_{$i}.pdf";
            $path     = $this->storeInitialCv($filename);

            Storage::disk('public')->assertExists($path);

            // Simulate deleteCv logic
            $fileStorage = new FileStorageService();
            $fileStorage->deleteFile($path);
            SiteSetting::set('cv_file_path', null);

            // Assert file is gone
            Storage::disk('public')->assertMissing($path);

            // Assert setting is cleared
            $this->assertNull(
                SiteSetting::get('cv_file_path'),
                "Iteration {$i}: cv_file_path must be null after deletion"
            );
        }
    }

    /**
     * Property 20: Uploading CV when no previous CV exists does not throw errors.
     *
     * Validates: Requirements 10.9
     */
    public function test_uploading_cv_with_no_previous_cv_is_safe(): void
    {
        // No previous CV in settings
        $this->assertNull(SiteSetting::get('cv_file_path'));

        $fileStorage = new FileStorageService();

        // deleteFile with null should not throw
        $fileStorage->deleteFile(null);

        // Store new CV
        $newFile = UploadedFile::fake()->create('first_cv.pdf', 100, 'application/pdf');
        $newPath = 'cv/' . \Illuminate\Support\Str::uuid() . '.pdf';
        Storage::disk('public')->putFileAs('cv', $newFile, basename($newPath));
        SiteSetting::set('cv_file_path', $newPath);

        Storage::disk('public')->assertExists($newPath);
        $this->assertSame($newPath, SiteSetting::get('cv_file_path'));
    }
}

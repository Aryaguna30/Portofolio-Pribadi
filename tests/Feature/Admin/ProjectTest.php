<?php

// Feature: portfolio-website, Property 20, 22, 23: ProjectController Tests

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\EncodedImage;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ImageManagerInterface;
use Tests\TestCase;

/**
 * Feature tests for Admin ProjectController.
 *
 * Property 22: Thumbnail Converted to WebP on Storage
 * Property 20: Orphan File Cleanup on File Replacement
 * Property 23: Backend HTML Sanitization Removes XSS Payloads
 *
 * Validates: Requirements 11.4, 11.10, 11.13
 */

// Alias the ImageFacadeStub so FileStorageService can resolve it
if (!class_exists('Intervention\Image\Laravel\Facades\Image')) {
    class_alias(\Tests\Unit\Stubs\ImageFacadeStub::class, 'Intervention\Image\Laravel\Facades\Image');
}

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create();

        // Bind a test purifier that strips script tags and event handlers
        $this->app->bind('purifier', fn () => new class {
            public function purify(string $html): string
            {
                $stripped = strip_tags($html, '<p><strong><em><ul><ol><li><br>');
                $stripped = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $stripped);
                return $stripped;
            }
        });
    }

    // -------------------------------------------------------------------------
    // Helper: mock the Intervention Image manager
    // -------------------------------------------------------------------------

    private function mockImageManager(int $times = 1): void
    {
        $encodedMock = \Mockery::mock(EncodedImage::class);
        $encodedMock->shouldReceive('__toString')->andReturn('fake-webp-data');
        $encodedMock->shouldReceive('toString')->andReturn('fake-webp-data');

        $imageMock = \Mockery::mock(ImageInterface::class);
        $imageMock->shouldReceive('toWebp')->with(85)->andReturn($encodedMock);

        $managerMock = \Mockery::mock(ImageManagerInterface::class);
        $managerMock->shouldReceive('read')
            ->times($times)
            ->andReturn($imageMock);

        $this->app->instance('image', $managerMock);
        \Tests\Unit\Stubs\ImageFacadeStub::setApp($this->app);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title'        => 'Proyek Test',
            'description'  => '<p>Deskripsi proyek ini.</p>',
            'tech_stack'   => ['Laravel', 'Vue.js'],
            'is_published' => true,
            'sort_order'   => 0,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Property 22: Thumbnail Converted to WebP on Storage
    // -------------------------------------------------------------------------

    /**
     * Property 22: JPEG uploaded as thumbnail must be stored as WebP.
     *
     * Validates: Requirements 11.4
     */
    public function test_jpeg_thumbnail_is_stored_as_webp(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            $this->mockImageManager();

            $file    = UploadedFile::fake()->image("photo_{$i}.jpg", 100, 100);
            $payload = $this->validPayload(['thumbnail' => $file]);

            $response = $this->actingAs($this->admin)
                ->post(route('admin.projects.store'), $payload);

            $response->assertRedirect(route('admin.projects.index'));

            $project = Project::latest()->first();
            $this->assertNotNull($project, "Iteration {$i}: project must be created");
            $this->assertStringEndsWith('.webp', $project->thumbnail_path, "Iteration {$i}: thumbnail must be .webp");
            Storage::disk('public')->assertExists($project->thumbnail_path);

            // Clean up for next iteration
            $project->forceDelete();
        }
    }

    /**
     * Property 22: PNG uploaded as thumbnail must be stored as WebP.
     *
     * Validates: Requirements 11.4
     */
    public function test_png_thumbnail_is_stored_as_webp(): void
    {
        $this->mockImageManager();

        $file    = UploadedFile::fake()->image('photo.png', 200, 200);
        $payload = $this->validPayload(['thumbnail' => $file]);

        $this->actingAs($this->admin)
            ->post(route('admin.projects.store'), $payload);

        $project = Project::latest()->first();
        $this->assertNotNull($project);
        $this->assertStringEndsWith('.webp', $project->thumbnail_path);
        Storage::disk('public')->assertExists($project->thumbnail_path);
    }

    // -------------------------------------------------------------------------
    // Property 20: Orphan File Cleanup on File Replacement
    // -------------------------------------------------------------------------

    /**
     * Property 20: Old thumbnail must be deleted when a new one is uploaded on update.
     *
     * Validates: Requirements 11.13
     */
    public function test_old_thumbnail_deleted_on_update(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            Storage::fake('public');

            // Create project with an existing thumbnail
            $oldPath = "thumbnails/old_thumb_{$i}.webp";
            Storage::disk('public')->put($oldPath, 'fake-old-webp');

            $project = Project::create([
                'title'          => ['id' => "Proyek {$i}", 'en' => "Project {$i}"],
                'description'    => ['id' => '<p>Desc</p>', 'en' => '<p>Desc</p>'],
                'tech_stack'     => ['Laravel'],
                'thumbnail_path' => $oldPath,
                'is_published'   => true,
                'sort_order'     => $i,
            ]);

            Storage::disk('public')->assertExists($oldPath);

            // Upload a new thumbnail
            $this->mockImageManager();
            $newFile = UploadedFile::fake()->image("new_photo_{$i}.jpg", 100, 100);

            $this->actingAs($this->admin)
                ->put(route('admin.projects.update', $project), array_merge(
                    $this->validPayload(),
                    ['thumbnail' => $newFile]
                ));

            // Old file must be gone
            Storage::disk('public')->assertMissing($oldPath);

            // New file must exist
            $project->refresh();
            Storage::disk('public')->assertExists($project->thumbnail_path);

            $project->forceDelete();
        }
    }

    /**
     * Property 20: Thumbnail must be deleted when project is destroyed.
     *
     * Validates: Requirements 11.13
     */
    public function test_thumbnail_deleted_on_project_destroy(): void
    {
        $thumbPath = 'thumbnails/to_delete.webp';
        Storage::disk('public')->put($thumbPath, 'fake-webp');

        $project = Project::create([
            'title'          => ['id' => 'Proyek Hapus', 'en' => 'Delete Project'],
            'description'    => ['id' => '<p>Desc</p>', 'en' => '<p>Desc</p>'],
            'tech_stack'     => ['Laravel'],
            'thumbnail_path' => $thumbPath,
            'is_published'   => true,
            'sort_order'     => 0,
        ]);

        Storage::disk('public')->assertExists($thumbPath);

        $this->actingAs($this->admin)
            ->delete(route('admin.projects.destroy', $project));

        Storage::disk('public')->assertMissing($thumbPath);
    }

    // -------------------------------------------------------------------------
    // Property 23: Backend HTML Sanitization Removes XSS Payloads
    // -------------------------------------------------------------------------

    /**
     * Property 23: XSS payloads in description must be sanitized before saving.
     *
     * Validates: Requirements 11.10
     */
    public function test_xss_in_description_is_sanitized_on_store(): void
    {
        $xssPayloads = [
            '<script>alert(1)</script><p>Safe</p>',
            '<p>Hello</p><script>document.cookie</script>',
            '<img src=x onerror="alert(1)"><p>Text</p>',
            '<p onclick="evil()">Click me</p>',
            '<p>Normal</p><iframe src="evil.com"></iframe>',
        ];

        foreach ($xssPayloads as $i => $payload) {
            $this->mockImageManager();

            $file = UploadedFile::fake()->image("photo_{$i}.jpg", 100, 100);

            $this->actingAs($this->admin)
                ->post(route('admin.projects.store'), $this->validPayload([
                    'thumbnail'   => $file,
                    'description' => $payload,
                ]));

            $project = Project::latest()->first();
            $this->assertNotNull($project, "Iteration {$i}: project must be created");

            $savedDescription = is_array($project->description)
                ? ($project->description['id'] ?? '')
                : $project->description;

            $this->assertStringNotContainsString(
                '<script>',
                $savedDescription,
                "Iteration {$i}: <script> tag must be removed from saved description"
            );
            $this->assertStringNotContainsString(
                'onerror=',
                $savedDescription,
                "Iteration {$i}: onerror= must be removed from saved description"
            );

            $project->forceDelete();
        }
    }

    /**
     * Property 23: XSS payloads in description must be sanitized on update.
     *
     * Validates: Requirements 11.10
     */
    public function test_xss_in_description_is_sanitized_on_update(): void
    {
        $project = Project::create([
            'title'          => ['id' => 'Proyek', 'en' => 'Project'],
            'description'    => ['id' => '<p>Safe</p>', 'en' => '<p>Safe</p>'],
            'tech_stack'     => ['Laravel'],
            'thumbnail_path' => 'thumbnails/existing.webp',
            'is_published'   => true,
            'sort_order'     => 0,
        ]);

        $xssPayload = '<script>alert("xss")</script><p>Content</p>';

        $this->actingAs($this->admin)
            ->put(route('admin.projects.update', $project), $this->validPayload([
                'description' => $xssPayload,
            ]));

        $project->refresh();

        $savedDescription = is_array($project->description)
            ? ($project->description['id'] ?? '')
            : $project->description;

        $this->assertStringNotContainsString('<script>', $savedDescription);
    }
}

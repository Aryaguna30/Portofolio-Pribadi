<?php

// Feature: portfolio-website, Property 22: Thumbnail Converted to WebP on Storage

namespace Tests\Property;

use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\EncodedImage;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ImageManagerInterface;
use Tests\TestCase;

/**
 * Property 22: Thumbnail Converted to WebP on Storage
 *
 * Validates: Requirements 11.4
 *
 * Property: Any JPEG or PNG file uploaded as a project thumbnail must be stored
 * as a WebP file. The stored path must end with .webp and the file must exist
 * on the public storage disk.
 *
 * Tests FileStorageService::storeWebP() directly with mocked Image facade.
 * 5 iterations with different image inputs.
 */

// Alias the ImageFacadeStub so FileStorageService can resolve it
if (!class_exists('Intervention\Image\Laravel\Facades\Image')) {
    class_alias(\Tests\Unit\Stubs\ImageFacadeStub::class, 'Intervention\Image\Laravel\Facades\Image');
}

class ThumbnailWebPPropertyTest extends TestCase
{
    private FileStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new FileStorageService();
    }

    /**
     * Helper: register a mock ImageManagerInterface in the container.
     */
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

    /**
     * Property 22: JPEG/PNG uploaded must be stored as WebP.
     *
     * Validates: Requirements 11.4
     */
    public function test_jpeg_and_png_files_are_stored_as_webp(): void
    {
        $inputs = [
            ['filename' => 'photo1.jpg',  'width' => 100, 'height' => 100],
            ['filename' => 'photo2.png',  'width' => 200, 'height' => 150],
            ['filename' => 'image3.jpg',  'width' => 800, 'height' => 600],
            ['filename' => 'banner4.png', 'width' => 1200, 'height' => 400],
            ['filename' => 'thumb5.jpg',  'width' => 50,  'height' => 50],
        ];

        foreach ($inputs as $i => $input) {
            $this->mockImageManager();

            $file = UploadedFile::fake()->image(
                $input['filename'],
                $input['width'],
                $input['height']
            );

            $path = $this->service->storeWebP($file, 'thumbnails');

            $this->assertStringEndsWith(
                '.webp',
                $path,
                "Iteration {$i}: stored path must end with .webp (got: {$path})"
            );

            $this->assertStringStartsWith(
                'thumbnails/',
                $path,
                "Iteration {$i}: stored path must be under thumbnails/ directory"
            );

            $this->assertTrue(
                Storage::disk('public')->exists($path),
                "Iteration {$i}: WebP file must exist on public storage disk"
            );
        }
    }

    /**
     * Property 22: Each stored WebP file must have a unique path (UUID-based).
     *
     * Validates: Requirements 11.4
     */
    public function test_each_stored_webp_has_unique_path(): void
    {
        $this->mockImageManager(5);

        $paths = [];

        for ($i = 0; $i < 5; $i++) {
            $file   = UploadedFile::fake()->image("photo_{$i}.jpg", 100, 100);
            $paths[] = $this->service->storeWebP($file, 'thumbnails');
        }

        $uniquePaths = array_unique($paths);
        $this->assertCount(5, $uniquePaths, 'All 5 stored WebP paths must be unique');
    }
}

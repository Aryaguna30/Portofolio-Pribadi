<?php

namespace Tests\Unit;

use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\EncodedImage;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ImageManagerInterface;
use Tests\TestCase;

/**
 * Minimal stub for the Intervention Image Laravel facade.
 * The intervention/image-laravel package is not installed, so we define
 * a lightweight facade that delegates to the container binding.
 */
if (!class_exists('Intervention\Image\Laravel\Facades\Image')) {
    class_alias(\Tests\Unit\Stubs\ImageFacadeStub::class, 'Intervention\Image\Laravel\Facades\Image');
}

class FileStorageServiceTest extends TestCase
{
    private FileStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileStorageService();
        Storage::fake('public');
    }

    // -------------------------------------------------------------------------
    // storeWebP — path and extension
    // -------------------------------------------------------------------------

    public function test_store_webp_returns_path_with_webp_extension(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);
        $this->mockImageManager();

        $path = $this->service->storeWebP($file, 'thumbnails');

        $this->assertStringEndsWith('.webp', $path);
    }

    public function test_store_webp_stores_file_in_correct_directory(): void
    {
        $file = UploadedFile::fake()->image('photo.png', 100, 100);
        $this->mockImageManager();

        $path = $this->service->storeWebP($file, 'thumbnails');

        $this->assertStringStartsWith('thumbnails/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_store_webp_file_exists_on_disk_after_store(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 50, 50);
        $this->mockImageManager();

        $path = $this->service->storeWebP($file, 'projects');

        Storage::disk('public')->assertExists($path);
    }

    public function test_store_webp_generates_unique_filenames(): void
    {
        $this->mockImageManager(2);

        $file1 = UploadedFile::fake()->image('a.jpg', 10, 10);
        $file2 = UploadedFile::fake()->image('b.jpg', 10, 10);

        $path1 = $this->service->storeWebP($file1, 'thumbnails');
        $path2 = $this->service->storeWebP($file2, 'thumbnails');

        $this->assertNotEquals($path1, $path2);
    }

    // -------------------------------------------------------------------------
    // deleteFile
    // -------------------------------------------------------------------------

    public function test_delete_file_removes_existing_file(): void
    {
        Storage::disk('public')->put('thumbnails/old.webp', 'fake-content');
        Storage::disk('public')->assertExists('thumbnails/old.webp');

        $this->service->deleteFile('thumbnails/old.webp');

        Storage::disk('public')->assertMissing('thumbnails/old.webp');
    }

    public function test_delete_file_with_null_does_nothing(): void
    {
        $this->service->deleteFile(null);
        $this->assertTrue(true);
    }

    public function test_delete_file_with_empty_string_does_nothing(): void
    {
        $this->service->deleteFile('');
        $this->assertTrue(true);
    }

    public function test_delete_file_with_nonexistent_path_does_nothing(): void
    {
        $this->service->deleteFile('thumbnails/nonexistent.webp');
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Old file deletion on new upload
    // -------------------------------------------------------------------------

    public function test_old_file_is_deleted_when_new_file_is_uploaded(): void
    {
        Storage::disk('public')->put('thumbnails/old-image.webp', 'old-content');
        Storage::disk('public')->assertExists('thumbnails/old-image.webp');

        // Delete old file (as a controller would before calling storeWebP)
        $this->service->deleteFile('thumbnails/old-image.webp');

        $this->mockImageManager();
        $newFile = UploadedFile::fake()->image('new.jpg', 100, 100);
        $newPath = $this->service->storeWebP($newFile, 'thumbnails');

        Storage::disk('public')->assertMissing('thumbnails/old-image.webp');
        Storage::disk('public')->assertExists($newPath);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Register a mock ImageManagerInterface in the container so that the
     * ImageFacadeStub (aliased as Intervention\Image\Laravel\Facades\Image)
     * resolves to it when Image::read() is called.
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
}

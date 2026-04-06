<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FileStorageService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Convert an uploaded image to WebP and store it on the public disk.
     *
     * @param  UploadedFile  $file
     * @param  string  $directory  Subdirectory under storage/app/public (e.g. "thumbnails")
     * @return string  Relative path (e.g. "thumbnails/uuid.webp")
     */
    public function storeWebP(UploadedFile $file, string $directory): string
    {
        $filename     = Str::uuid() . '.webp';
        $relativePath = $directory . '/' . $filename;

        try {
            $image   = $this->imageManager->read($file->getRealPath());
            $encoded = $image->toWebp(85);
            Storage::disk('public')->put($relativePath, $encoded);
        } catch (\Throwable $e) {
            // Fallback: store original file if WebP conversion fails
            $ext          = $file->getClientOriginalExtension() ?: 'jpg';
            $relativePath = $directory . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->putFileAs(
                $directory,
                $file,
                basename($relativePath)
            );
        }

        return $relativePath;
    }

    /**
     * Delete a file from the public storage disk.
     *
     * @param  string|null  $path  Relative path (e.g. "thumbnails/uuid.webp")
     */
    public function deleteFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}

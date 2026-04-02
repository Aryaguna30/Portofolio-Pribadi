<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileStorageService
{
    /**
     * Convert an uploaded image to WebP and store it.
     *
     * @param  UploadedFile  $file
     * @param  string  $directory  Subdirectory under storage/app/public (e.g. "thumbnails")
     * @return string  Relative path (e.g. "thumbnails/uuid.webp")
     */
    public function storeWebP(UploadedFile $file, string $directory): string
    {
        $filename = Str::uuid() . '.webp';
        $relativePath = $directory . '/' . $filename;

        $image = Image::read($file->getRealPath());
        $encoded = $image->toWebp(85);

        Storage::disk('public')->put($relativePath, $encoded);

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

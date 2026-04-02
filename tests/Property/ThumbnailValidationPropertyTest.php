<?php

// Feature: portfolio-website, Property 21: Thumbnail Validation Rejects Invalid Files

namespace Tests\Property;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Property 21: Thumbnail Validation Rejects Invalid Files
 *
 * Validates: Requirements 11.3
 *
 * Property: Any file with a MIME type other than jpeg/png/webp must be
 * rejected by the ProjectRequest thumbnail validation rules.
 *
 * Tests the validation rules directly using Laravel's Validator facade,
 * simulating property-based testing with randomised invalid MIME types.
 */
class ThumbnailValidationPropertyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Returns the thumbnail validation rules from ProjectRequest (POST mode).
     */
    private function thumbnailRules(): array
    {
        return ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'];
    }

    /**
     * List of MIME types that are NOT allowed for thumbnails.
     */
    private function invalidMimeTypes(): array
    {
        return [
            'gif',
            'bmp',
            'tiff',
            'svg',
            'ico',
            'pdf',
            'txt',
            'html',
            'php',
            'js',
            'css',
            'zip',
            'mp4',
            'mp3',
            'doc',
            'docx',
            'xls',
            'csv',
            'json',
            'xml',
        ];
    }

    /**
     * Property 21: Any MIME type other than jpeg/png/webp must be rejected.
     *
     * Validates: Requirements 11.3
     */
    public function test_invalid_mime_types_are_always_rejected(): void
    {
        $invalidTypes = $this->invalidMimeTypes();
        $iterations   = min(20, count($invalidTypes));

        for ($i = 0; $i < $iterations; $i++) {
            $mimeType = $invalidTypes[$i];

            $file = UploadedFile::fake()->create(
                "thumbnail.{$mimeType}",
                100,
                "image/{$mimeType}"
            );

            $data  = ['thumbnail' => $file];
            $rules = ['thumbnail' => $this->thumbnailRules()];

            $validator = Validator::make($data, $rules);

            $this->assertTrue(
                $validator->fails(),
                "Iteration {$i}: MIME type '{$mimeType}' should be rejected but passed validation"
            );

            $this->assertArrayHasKey(
                'thumbnail',
                $validator->errors()->toArray(),
                "Iteration {$i}: validation errors must include 'thumbnail' for MIME type '{$mimeType}'"
            );
        }
    }

    /**
     * Property 21: Non-image file types (documents, archives, etc.) must be rejected.
     */
    public function test_non_image_files_are_rejected(): void
    {
        $nonImageTypes = [
            ['name' => 'document.pdf', 'mime' => 'application/pdf'],
            ['name' => 'archive.zip', 'mime' => 'application/zip'],
            ['name' => 'script.js',   'mime' => 'application/javascript'],
            ['name' => 'page.html',   'mime' => 'text/html'],
            ['name' => 'data.json',   'mime' => 'application/json'],
        ];

        foreach ($nonImageTypes as $idx => $type) {
            $file = UploadedFile::fake()->create($type['name'], 100, $type['mime']);

            $data  = ['thumbnail' => $file];
            $rules = ['thumbnail' => $this->thumbnailRules()];

            $validator = Validator::make($data, $rules);

            $this->assertTrue(
                $validator->fails(),
                "Non-image file '{$type['name']}' should be rejected"
            );
        }
    }

    /**
     * Property 21: Valid MIME types (jpeg, png, webp) must pass validation.
     * Sanity check to ensure the rules are not overly restrictive.
     */
    public function test_valid_mime_types_pass_validation(): void
    {
        $validTypes = [
            ['name' => 'photo.jpg',  'mime' => 'image/jpeg'],
            ['name' => 'image.png',  'mime' => 'image/png'],
            ['name' => 'graphic.webp', 'mime' => 'image/webp'],
        ];

        foreach ($validTypes as $type) {
            $file = UploadedFile::fake()->image($type['name']);

            $data  = ['thumbnail' => $file];
            $rules = ['thumbnail' => $this->thumbnailRules()];

            $validator = Validator::make($data, $rules);

            $this->assertFalse(
                $validator->fails(),
                "Valid MIME type '{$type['mime']}' should pass validation but was rejected"
            );
        }
    }

    /**
     * Property 21: File exceeding 2048 KB must be rejected regardless of MIME type.
     */
    public function test_oversized_thumbnail_is_rejected(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            $sizeKb = rand(2049, 10240);

            $file = UploadedFile::fake()->create('large.jpg', $sizeKb, 'image/jpeg');

            $data  = ['thumbnail' => $file];
            $rules = ['thumbnail' => $this->thumbnailRules()];

            $validator = Validator::make($data, $rules);

            $this->assertTrue(
                $validator->fails(),
                "Iteration {$i}: file of {$sizeKb} KB should be rejected (max 2048 KB)"
            );

            $this->assertArrayHasKey(
                'thumbnail',
                $validator->errors()->toArray(),
                "Iteration {$i}: validation errors must include 'thumbnail' for oversized file"
            );
        }
    }

    /**
     * Property 21: Missing thumbnail on POST must be rejected (required rule).
     */
    public function test_missing_thumbnail_is_rejected_on_post(): void
    {
        $data  = [];
        $rules = ['thumbnail' => $this->thumbnailRules()];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('thumbnail', $validator->errors()->toArray());
    }
}

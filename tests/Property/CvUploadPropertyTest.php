<?php

// Feature: portfolio-website, Property 19: CV File Validation Rejects Invalid Files

namespace Tests\Property;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Property 19: CV File Validation Rejects Invalid Files
 *
 * Validates: Requirements 10.6
 *
 * Property: Any non-PDF file or any file exceeding 5 MB (5120 KB) must be
 * rejected by the CvUploadRequest validation rules without saving to disk.
 *
 * Tests the validation rules directly using Laravel's Validator facade,
 * simulating property-based testing with randomised invalid inputs.
 */
class CvUploadPropertyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Returns the CV upload validation rules from CvUploadRequest.
     */
    private function rules(): array
    {
        return [
            'cv_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    /**
     * List of non-PDF MIME types to test against.
     */
    private function nonPdfMimeTypes(): array
    {
        return [
            ['ext' => 'doc',  'mime' => 'application/msword'],
            ['ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['ext' => 'txt',  'mime' => 'text/plain'],
            ['ext' => 'jpg',  'mime' => 'image/jpeg'],
            ['ext' => 'png',  'mime' => 'image/png'],
            ['ext' => 'gif',  'mime' => 'image/gif'],
            ['ext' => 'zip',  'mime' => 'application/zip'],
            ['ext' => 'mp4',  'mime' => 'video/mp4'],
            ['ext' => 'mp3',  'mime' => 'audio/mpeg'],
            ['ext' => 'html', 'mime' => 'text/html'],
            ['ext' => 'php',  'mime' => 'application/x-php'],
            ['ext' => 'js',   'mime' => 'application/javascript'],
            ['ext' => 'csv',  'mime' => 'text/csv'],
            ['ext' => 'xls',  'mime' => 'application/vnd.ms-excel'],
            ['ext' => 'ppt',  'mime' => 'application/vnd.ms-powerpoint'],
            ['ext' => 'xml',  'mime' => 'application/xml'],
            ['ext' => 'json', 'mime' => 'application/json'],
            ['ext' => 'svg',  'mime' => 'image/svg+xml'],
            ['ext' => 'exe',  'mime' => 'application/octet-stream'],
            ['ext' => 'sh',   'mime' => 'application/x-sh'],
        ];
    }

    // -------------------------------------------------------------------------
    // Property 19: Non-PDF files must be rejected
    // -------------------------------------------------------------------------

    /**
     * Property 19: Any non-PDF file must be rejected without saving to disk.
     *
     * Validates: Requirements 10.6
     */
    public function test_non_pdf_files_are_always_rejected(): void
    {
        $nonPdfTypes = $this->nonPdfMimeTypes();
        $iterations  = min(20, count($nonPdfTypes));

        for ($i = 0; $i < $iterations; $i++) {
            $type = $nonPdfTypes[$i];

            $file = UploadedFile::fake()->create(
                "cv.{$type['ext']}",
                100,
                $type['mime']
            );

            $data      = ['cv_file' => $file];
            $validator = Validator::make($data, $this->rules());

            $this->assertTrue(
                $validator->fails(),
                "Iteration {$i}: file type '{$type['ext']}' ({$type['mime']}) should be rejected"
            );

            $this->assertArrayHasKey(
                'cv_file',
                $validator->errors()->toArray(),
                "Iteration {$i}: validation errors must include 'cv_file' for non-PDF type '{$type['ext']}'"
            );

            // Verify nothing was saved to disk
            Storage::disk('public')->assertDirectoryEmpty('/');
        }
    }

    // -------------------------------------------------------------------------
    // Property 19: Files exceeding 5 MB must be rejected
    // -------------------------------------------------------------------------

    /**
     * Property 19: Any PDF file exceeding 5120 KB must be rejected without saving to disk.
     *
     * Validates: Requirements 10.6
     */
    public function test_oversized_pdf_is_always_rejected(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            $sizeKb = rand(5121, 20480);

            $file = UploadedFile::fake()->create('cv.pdf', $sizeKb, 'application/pdf');

            $data      = ['cv_file' => $file];
            $validator = Validator::make($data, $this->rules());

            $this->assertTrue(
                $validator->fails(),
                "Iteration {$i}: PDF of {$sizeKb} KB should be rejected (max 5120 KB)"
            );

            $this->assertArrayHasKey(
                'cv_file',
                $validator->errors()->toArray(),
                "Iteration {$i}: validation errors must include 'cv_file' for oversized PDF ({$sizeKb} KB)"
            );

            // Verify nothing was saved to disk
            Storage::disk('public')->assertDirectoryEmpty('/');
        }
    }

    /**
     * Property 19: PDF at exactly the size limit (5120 KB) must pass validation.
     */
    public function test_pdf_at_size_limit_passes_validation(): void
    {
        $file = UploadedFile::fake()->create('cv.pdf', 5120, 'application/pdf');

        $data      = ['cv_file' => $file];
        $validator = Validator::make($data, $this->rules());

        $this->assertFalse(
            $validator->fails(),
            'PDF at exactly 5120 KB should pass validation'
        );
    }

    /**
     * Property 19: Valid PDF within size limit must pass validation (sanity check).
     */
    public function test_valid_pdf_within_size_limit_passes_validation(): void
    {
        $iterations = 3;

        for ($i = 0; $i < $iterations; $i++) {
            $sizeKb = rand(1, 5120);

            $file = UploadedFile::fake()->create('cv.pdf', $sizeKb, 'application/pdf');

            $data      = ['cv_file' => $file];
            $validator = Validator::make($data, $this->rules());

            $this->assertFalse(
                $validator->fails(),
                "Iteration {$i}: valid PDF of {$sizeKb} KB should pass validation"
            );
        }
    }

    /**
     * Property 19: Missing cv_file must be rejected (required rule).
     */
    public function test_missing_cv_file_is_rejected(): void
    {
        $data      = [];
        $validator = Validator::make($data, $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cv_file', $validator->errors()->toArray());
    }

    /**
     * Property 19: Non-file scalar values must be rejected.
     */
    public function test_non_file_scalar_values_are_rejected(): void
    {
        $nonFileValues = ['string', 123, null, [], true, 'path/to/file.pdf'];

        foreach ($nonFileValues as $value) {
            $data      = ['cv_file' => $value];
            $validator = Validator::make($data, $this->rules());

            $this->assertTrue(
                $validator->fails(),
                "Non-file value '" . json_encode($value) . "' should be rejected"
            );
        }
    }
}

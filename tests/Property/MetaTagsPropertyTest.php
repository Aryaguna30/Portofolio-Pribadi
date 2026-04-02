<?php

// Feature: portfolio-website, Property 27: Meta Tags Present in Every Page Response

namespace Tests\Property;

use App\Models\SiteSetting;
use App\Services\MetaTagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property 27: Meta Tags Present in Every Page Response
 *
 * Validates: Requirements 16.1, 16.2
 *
 * Property: MetaTagService::forHome() must always return an array containing
 * title, description, ogTitle, ogDescription, and ogUrl with non-empty values.
 *
 * Simulates property-based testing by running multiple randomised hero
 * settings and asserting that all required meta tag fields are present
 * and non-empty in the returned array.
 */
class MetaTagsPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Required meta tag keys that must always be present and non-empty.
     */
    private array $requiredKeys = [
        'title',
        'description',
        'ogTitle',
        'ogDescription',
        'ogUrl',
    ];

    /**
     * Generate a random hero name.
     */
    private function randomName(): string
    {
        $names = [
            'Ahmad Fauzi',
            'Budi Santoso',
            'Citra Dewi',
            'Dian Pratama',
            'Developer ' . rand(1, 999),
            'Full Stack Engineer',
            'Jane Doe',
            'John Smith',
        ];

        return $names[array_rand($names)];
    }

    /**
     * Property 27: MetaTagService::forHome() must return all required fields
     * with non-empty values for any hero name setting.
     *
     * Validates: Requirements 16.1, 16.2
     */
    public function test_for_home_returns_all_required_meta_fields(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            SiteSetting::truncate();

            $name = $this->randomName();
            SiteSetting::set('hero_name', $name);

            $service = app(MetaTagService::class);
            $meta    = $service->forHome();

            foreach ($this->requiredKeys as $key) {
                $this->assertArrayHasKey(
                    $key,
                    $meta,
                    "Iteration {$i}: meta array must contain key '{$key}'"
                );

                $this->assertNotEmpty(
                    $meta[$key],
                    "Iteration {$i}: meta['{$key}'] must not be empty (hero_name='{$name}')"
                );

                $this->assertIsString(
                    $meta[$key],
                    "Iteration {$i}: meta['{$key}'] must be a string"
                );
            }
        }
    }

    /**
     * Property 27: title must contain the hero name.
     *
     * Validates: Requirements 16.1
     */
    public function test_title_contains_hero_name(): void
    {
        $names = ['Ahmad Fauzi', 'Budi Santoso', 'Citra Dewi'];

        foreach ($names as $name) {
            SiteSetting::truncate();
            SiteSetting::set('hero_name', $name);

            $service = app(MetaTagService::class);
            $meta    = $service->forHome();

            $this->assertStringContainsString(
                $name,
                $meta['title'],
                "title must contain hero name '{$name}'"
            );

            $this->assertStringContainsString(
                $name,
                $meta['ogTitle'],
                "ogTitle must contain hero name '{$name}'"
            );
        }
    }

    /**
     * Property 27: ogUrl must be a valid absolute URL.
     *
     * Validates: Requirements 16.2
     */
    public function test_og_url_is_absolute_url(): void
    {
        SiteSetting::set('hero_name', 'Test Developer');

        $service = app(MetaTagService::class);
        $meta    = $service->forHome();

        $this->assertStringStartsWith(
            'http',
            $meta['ogUrl'],
            "ogUrl must be an absolute URL starting with 'http'"
        );
    }

    /**
     * Property 27: All required fields are present even when hero_name is not set.
     *
     * Validates: Requirements 16.1, 16.2
     */
    public function test_required_fields_present_with_default_settings(): void
    {
        // No settings saved — should fall back to app name
        $service = app(MetaTagService::class);
        $meta    = $service->forHome();

        foreach ($this->requiredKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $meta,
                "meta array must contain key '{$key}' even with default settings"
            );

            $this->assertNotEmpty(
                $meta[$key],
                "meta['{$key}'] must not be empty with default settings"
            );
        }
    }

    /**
     * Property 27: description and ogDescription must be non-empty strings.
     *
     * Validates: Requirements 16.1, 16.2
     */
    public function test_description_fields_are_non_empty_strings(): void
    {
        $iterations = 3;

        for ($i = 0; $i < $iterations; $i++) {
            SiteSetting::truncate();
            SiteSetting::set('hero_name', $this->randomName());

            $service = app(MetaTagService::class);
            $meta    = $service->forHome();

            $this->assertNotEmpty(
                $meta['description'],
                "Iteration {$i}: description must not be empty"
            );

            $this->assertNotEmpty(
                $meta['ogDescription'],
                "Iteration {$i}: ogDescription must not be empty"
            );

            $this->assertGreaterThan(
                0,
                strlen($meta['description']),
                "Iteration {$i}: description must have length > 0"
            );
        }
    }
}

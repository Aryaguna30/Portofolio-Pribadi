<?php

// Feature: portfolio-website, Property 6: Hero Content Round Trip

namespace Tests\Property;

use App\Http\Controllers\HomeController;
use App\Models\SiteSetting;
use App\Services\MetaTagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Inertia\Support\Header;
use Tests\TestCase;

/**
 * Property 6: Hero Content Round Trip
 *
 * Validates: Requirements 3.6, 10.2
 *
 * Property: Any hero settings saved to SiteSetting must be returned
 * identically by HomeController::index() in the 'hero' prop.
 *
 * Simulates property-based testing by running multiple randomised hero
 * data sets and asserting round-trip identity.
 */
class HeroContentPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Resolve the hero props from HomeController::index() by sending an
     * Inertia XHR request (X-Inertia header) which returns a JSON response.
     */
    private function getHeroProps(): array
    {
        $metaTagService  = app(MetaTagService::class);
        $controller      = new HomeController($metaTagService);
        $inertiaResponse = $controller->index();

        // Build a fake Inertia XHR request so toResponse() returns JSON
        $request = Request::create('/', 'GET');
        $request->headers->set(Header::INERTIA, 'true');

        $httpResponse = $inertiaResponse->toResponse($request);

        // JsonResponse::getData(true) returns the decoded array
        return $httpResponse->getData(true)['props'] ?? [];
    }

    /**
     * Generate a random hero name string.
     */
    private function randomName(): string
    {
        $names = [
            'Ahmad Fauzi',
            'Budi Santoso',
            'Citra Dewi',
            'Dian Pratama',
            str_repeat('X', rand(1, 50)),
            'Developer ' . rand(1, 999),
            'Full Stack Engineer',
            'Jane Doe',
        ];

        return $names[array_rand($names)];
    }

    /**
     * Generate a random array of profession strings.
     */
    private function randomProfessions(): array
    {
        $pool = [
            'Full Stack Developer',
            'Backend Engineer',
            'Frontend Developer',
            'Laravel Specialist',
            'Vue.js Developer',
            'Software Engineer',
            'Web Developer',
            'API Developer',
        ];

        shuffle($pool);
        $count = rand(1, 4);

        return array_slice($pool, 0, $count);
    }

    /**
     * Generate a random hero description string.
     */
    private function randomDescription(): string
    {
        $descriptions = [
            'Passionate developer building modern web applications.',
            'Experienced in Laravel, Vue.js, and Tailwind CSS.',
            str_repeat('Lorem ipsum ', rand(1, 10)),
            'Building scalable solutions.',
            'Developer with ' . rand(1, 10) . '+ years of experience.',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Generate a random typing speed integer.
     */
    private function randomTypingSpeed(): int
    {
        return rand(50, 300);
    }

    /**
     * Property 6: Hero data saved to SiteSetting must be returned
     * identically by HomeController::index().
     *
     * Validates: Requirements 3.6, 10.2
     */
    public function test_hero_content_round_trip_is_identical(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            // Clear settings between iterations
            SiteSetting::truncate();

            $name        = $this->randomName();
            $professions = $this->randomProfessions();
            $description = $this->randomDescription();
            $typingSpeed = $this->randomTypingSpeed();

            // Save hero settings to SiteSetting
            SiteSetting::set('hero_name', $name);
            SiteSetting::set('hero_professions', $professions);
            SiteSetting::set('hero_description', $description);
            SiteSetting::set('typing_speed', $typingSpeed);

            // Retrieve hero props from HomeController
            $props = $this->getHeroProps();
            $hero  = $props['hero'] ?? null;

            $this->assertNotNull(
                $hero,
                "Iteration {$i}: 'hero' prop must be present in HomeController response"
            );

            $this->assertSame(
                $name,
                $hero['name'],
                "Iteration {$i}: hero.name must match saved value"
            );

            $this->assertSame(
                $professions,
                $hero['professions'],
                "Iteration {$i}: hero.professions must match saved array"
            );

            $this->assertSame(
                $description,
                $hero['description'],
                "Iteration {$i}: hero.description must match saved value"
            );

            $this->assertSame(
                $typingSpeed,
                $hero['typingSpeed'],
                "Iteration {$i}: hero.typingSpeed must match saved integer"
            );
        }
    }

    /**
     * Property 6: Empty professions array is returned as empty array (not null).
     *
     * Validates: Requirements 3.6
     */
    public function test_empty_professions_returns_empty_array(): void
    {
        SiteSetting::set('hero_name', 'Test Developer');
        SiteSetting::set('hero_professions', []);
        SiteSetting::set('hero_description', 'A description.');
        SiteSetting::set('typing_speed', 100);

        $props = $this->getHeroProps();
        $hero  = $props['hero'] ?? null;

        $this->assertNotNull($hero);
        $this->assertIsArray($hero['professions']);
        $this->assertEmpty($hero['professions']);
    }

    /**
     * Property 6: typingSpeed is always cast to integer in the response.
     *
     * Validates: Requirements 3.6
     */
    public function test_typing_speed_is_always_integer(): void
    {
        $speeds = [50, 150, 250];

        foreach ($speeds as $speed) {
            SiteSetting::truncate();
            SiteSetting::set('hero_name', 'Dev');
            SiteSetting::set('hero_professions', ['Developer']);
            SiteSetting::set('hero_description', 'Description here.');
            SiteSetting::set('typing_speed', $speed);

            $props = $this->getHeroProps();
            $hero  = $props['hero'] ?? null;

            $this->assertNotNull($hero);
            $this->assertIsInt(
                $hero['typingSpeed'],
                "typingSpeed must be an integer for speed={$speed}"
            );
            $this->assertSame($speed, $hero['typingSpeed']);
        }
    }

    /**
     * Property 6: Missing settings return safe defaults (no crash).
     *
     * Validates: Requirements 3.6
     */
    public function test_missing_settings_return_safe_defaults(): void
    {
        // No settings saved — all should return defaults
        $props = $this->getHeroProps();
        $hero  = $props['hero'] ?? null;

        $this->assertNotNull($hero, "'hero' prop must always be present");
        $this->assertArrayHasKey('name', $hero);
        $this->assertArrayHasKey('professions', $hero);
        $this->assertArrayHasKey('description', $hero);
        $this->assertArrayHasKey('typingSpeed', $hero);
        $this->assertIsArray($hero['professions']);
        $this->assertIsInt($hero['typingSpeed']);
    }
}

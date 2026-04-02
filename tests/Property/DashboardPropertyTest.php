<?php

// Feature: portfolio-website, Property 18: Dashboard Statistics Match Database State

namespace Tests\Property;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Support\Header;
use Tests\TestCase;

/**
 * Property 18: Dashboard Statistics Match Database State
 *
 * Validates: Requirements 9.1, 9.2
 *
 * Property: DashboardController::index() must return stats that exactly match
 * the actual counts in the database for any given DB state.
 *
 * Simulates property-based testing by running 5 iterations with random DB states.
 */
class DashboardPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Resolve the dashboard props from DashboardController::index().
     */
    private function getDashboardProps(): array
    {
        $controller      = new DashboardController();
        $inertiaResponse = $controller->index();

        $request = Request::create('/admin/dashboard', 'GET');
        $request->headers->set(Header::INERTIA, 'true');

        $httpResponse = $inertiaResponse->toResponse($request);

        return $httpResponse->getData(true)['props'] ?? [];
    }

    /**
     * Property 18: Dashboard stats must match actual DB counts for any random state.
     *
     * Validates: Requirements 9.1, 9.2
     */
    public function test_dashboard_statistics_match_database_state(): void
    {
        $iterations = 5;

        for ($i = 0; $i < $iterations; $i++) {
            // Reset DB state between iterations
            Project::truncate();
            Message::truncate();

            // Create random number of published projects
            $publishedCount = rand(0, 5);
            $unpublishedCount = rand(0, 3);

            for ($p = 0; $p < $publishedCount; $p++) {
                Project::create([
                    'title'          => ['id' => "Project {$p}", 'en' => "Project {$p}"],
                    'description'    => ['id' => 'Desc', 'en' => 'Desc'],
                    'tech_stack'     => ['Laravel'],
                    'thumbnail_path' => "thumbnails/project_{$i}_{$p}.webp",
                    'is_published'   => true,
                    'sort_order'     => $p,
                ]);
            }

            for ($p = 0; $p < $unpublishedCount; $p++) {
                Project::create([
                    'title'          => ['id' => "Draft {$p}", 'en' => "Draft {$p}"],
                    'description'    => ['id' => 'Desc', 'en' => 'Desc'],
                    'tech_stack'     => ['Laravel'],
                    'thumbnail_path' => "thumbnails/draft_{$i}_{$p}.webp",
                    'is_published'   => false,
                    'sort_order'     => $p,
                ]);
            }

            // Create random number of messages (some read, some unread)
            $totalMessages = rand(0, 8);
            $unreadCount   = rand(0, $totalMessages);
            $readCount     = $totalMessages - $unreadCount;

            for ($m = 0; $m < $unreadCount; $m++) {
                Message::create([
                    'name'    => "Visitor {$m}",
                    'email'   => "visitor{$m}@example.com",
                    'subject' => 'Hello',
                    'body'    => 'Test message body',
                    'is_read' => false,
                ]);
            }

            for ($m = 0; $m < $readCount; $m++) {
                Message::create([
                    'name'    => "Read Visitor {$m}",
                    'email'   => "read{$m}@example.com",
                    'subject' => 'Hello',
                    'body'    => 'Test message body',
                    'is_read' => true,
                ]);
            }

            // Get actual DB counts
            $expectedPublished = Project::where('is_published', true)->count();
            $expectedTotal     = Message::count();
            $expectedUnread    = Message::where('is_read', false)->count();

            // Get dashboard props
            $props = $this->getDashboardProps();

            $this->assertArrayHasKey(
                'totalProjects',
                $props,
                "Iteration {$i}: 'totalProjects' must be present in dashboard props"
            );

            $this->assertArrayHasKey(
                'totalMessages',
                $props,
                "Iteration {$i}: 'totalMessages' must be present in dashboard props"
            );

            $this->assertArrayHasKey(
                'unreadMessages',
                $props,
                "Iteration {$i}: 'unreadMessages' must be present in dashboard props"
            );

            $this->assertSame(
                $expectedPublished,
                $props['totalProjects'],
                "Iteration {$i}: totalProjects ({$props['totalProjects']}) must match DB count ({$expectedPublished})"
            );

            $this->assertSame(
                $expectedTotal,
                $props['totalMessages'],
                "Iteration {$i}: totalMessages ({$props['totalMessages']}) must match DB count ({$expectedTotal})"
            );

            $this->assertSame(
                $expectedUnread,
                $props['unreadMessages'],
                "Iteration {$i}: unreadMessages ({$props['unreadMessages']}) must match DB count ({$expectedUnread})"
            );
        }
    }

    /**
     * Property 18: Unpublished projects must NOT be counted in totalProjects.
     *
     * Validates: Requirements 9.1
     */
    public function test_unpublished_projects_are_excluded_from_total(): void
    {
        Project::create([
            'title'          => ['id' => 'Draft', 'en' => 'Draft'],
            'description'    => ['id' => 'Desc', 'en' => 'Desc'],
            'tech_stack'     => ['Laravel'],
            'thumbnail_path' => 'thumbnails/draft.webp',
            'is_published'   => false,
            'sort_order'     => 0,
        ]);

        $props = $this->getDashboardProps();

        $this->assertSame(0, $props['totalProjects'], 'Unpublished projects must not be counted');
    }

    /**
     * Property 18: Empty database returns all zeros.
     *
     * Validates: Requirements 9.1, 9.2
     */
    public function test_empty_database_returns_zero_stats(): void
    {
        $props = $this->getDashboardProps();

        $this->assertSame(0, $props['totalProjects']);
        $this->assertSame(0, $props['totalMessages']);
        $this->assertSame(0, $props['unreadMessages']);
    }
}

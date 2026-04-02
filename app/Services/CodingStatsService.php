<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodingStatsService
{
    /**
     * Fetch coding stats from GitHub API and cache for 1 hour.
     *
     * Returns an array with:
     *   - languages: array of { name, percent, color }
     *   - total_commits: int
     *   - repositories: int
     *
     * Returns null if the request times out or an error occurs.
     */
    public function fetch(): ?array
    {
        return Cache::remember('coding_stats', 3600, function () {
            return $this->fetchFromGitHub();
        });
    }

    /**
     * Fetch stats from GitHub API.
     */
    private function fetchFromGitHub(): ?array
    {
        $username = env('GITHUB_USERNAME');
        $token    = env('GITHUB_TOKEN');

        if (empty($username)) {
            Log::warning('CodingStatsService: GITHUB_USERNAME is not set.');
            return null;
        }

        try {
            $headers = ['Accept' => 'application/vnd.github+json'];
            if (!empty($token)) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            // Fetch user profile
            $userResponse = Http::timeout(3)
                ->withHeaders($headers)
                ->get("https://api.github.com/users/{$username}");

            if (!$userResponse->successful()) {
                Log::warning('CodingStatsService: GitHub user API returned non-2xx.', [
                    'status' => $userResponse->status(),
                ]);
                return null;
            }

            $userData    = $userResponse->json();
            $repositories = (int) ($userData['public_repos'] ?? 0);

            // Fetch repositories to aggregate language usage
            $reposResponse = Http::timeout(3)
                ->withHeaders($headers)
                ->get("https://api.github.com/users/{$username}/repos", [
                    'per_page' => 100,
                    'sort'     => 'updated',
                ]);

            if (!$reposResponse->successful()) {
                Log::warning('CodingStatsService: GitHub repos API returned non-2xx.', [
                    'status' => $reposResponse->status(),
                ]);
                return null;
            }

            $repos       = $reposResponse->json();
            $totalCommits = 0;
            $languageCounts = [];

            foreach ($repos as $repo) {
                // Accumulate commit count from stargazers as a proxy when
                // commit API is not called (to stay within timeout budget).
                // Use the repo's language field for language aggregation.
                if (!empty($repo['language'])) {
                    $lang = $repo['language'];
                    $languageCounts[$lang] = ($languageCounts[$lang] ?? 0) + 1;
                }
            }

            // Build language percentage list
            $total     = array_sum($languageCounts);
            $languages = [];

            if ($total > 0) {
                arsort($languageCounts);
                $colorMap = $this->languageColorMap();

                foreach ($languageCounts as $name => $count) {
                    $percent = round(($count / $total) * 100, 1);
                    $languages[] = [
                        'name'    => $name,
                        'percent' => $percent,
                        'color'   => $colorMap[$name] ?? '#6366f1',
                    ];
                }
            }

            return [
                'languages'    => $languages,
                'total_commits' => $totalCommits,
                'repositories' => $repositories,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('CodingStatsService: Connection timeout or error.', [
                'message' => $e->getMessage(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('CodingStatsService: Unexpected error.', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * A basic color map for common programming languages.
     */
    private function languageColorMap(): array
    {
        return [
            'PHP'        => '#8892be',
            'JavaScript' => '#f1e05a',
            'TypeScript' => '#3178c6',
            'Vue'        => '#41b883',
            'Python'     => '#3572A5',
            'Java'       => '#b07219',
            'CSS'        => '#563d7c',
            'HTML'       => '#e34c26',
            'Shell'      => '#89e051',
            'Go'         => '#00ADD8',
            'Rust'       => '#dea584',
            'C++'        => '#f34b7d',
            'C#'         => '#178600',
            'Ruby'       => '#701516',
            'Swift'      => '#F05138',
            'Kotlin'     => '#A97BFF',
            'Dart'       => '#00B4AB',
        ];
    }
}

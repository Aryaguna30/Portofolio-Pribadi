<?php

namespace App\Http\Controllers;

use App\Services\CodingStatsService;
use Illuminate\Http\JsonResponse;

class CodingStatsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private CodingStatsService $codingStatsService) {}

    /**
     * Return coding stats as JSON.
     *
     * Requirements: 6.2, 6.4, 6.5
     *
     * - Calls CodingStatsService::fetch()
     * - Returns JSON with stats data on success
     * - Returns JSON with error flag if data is null (API failure / timeout)
     */
    public function index(): JsonResponse
    {
        $stats = $this->codingStatsService->fetch();

        if ($stats === null) {
            return response()->json([
                'error' => true,
                'data'  => null,
            ], 200);
        }

        return response()->json([
            'error' => false,
            'data'  => $stats,
        ], 200);
    }
}

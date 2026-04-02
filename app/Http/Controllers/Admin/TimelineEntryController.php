<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TimelineEntryRequest;
use App\Models\TimelineEntry;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TimelineEntryController extends Controller
{
    /**
     * Display a listing of all timeline entries.
     *
     * Requirements: 10.3, 10.4
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Timeline', [
            'entries' => TimelineEntry::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created timeline entry.
     *
     * Requirements: 10.3, 10.4
     */
    public function store(TimelineEntryRequest $request): JsonResponse
    {
        $entry = TimelineEntry::create($request->validated());

        return response()->json($entry, 201);
    }

    /**
     * Display the specified timeline entry.
     *
     * Requirements: 10.3, 10.4
     */
    public function show(TimelineEntry $timeline): Response
    {
        return Inertia::render('Admin/Timeline', [
            'entry' => $timeline,
        ]);
    }

    /**
     * Update the specified timeline entry.
     *
     * Requirements: 10.3, 10.4
     */
    public function update(TimelineEntryRequest $request, TimelineEntry $timeline): JsonResponse
    {
        $timeline->update($request->validated());

        return response()->json($timeline);
    }

    /**
     * Remove the specified timeline entry.
     *
     * Requirements: 10.3, 10.4
     */
    public function destroy(TimelineEntry $timeline): JsonResponse
    {
        $timeline->delete();

        return response()->json(null, 204);
    }
}

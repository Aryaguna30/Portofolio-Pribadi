<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TimelineEntryRequest;
use App\Models\TimelineEntry;
use Illuminate\Http\RedirectResponse;

class TimelineEntryController extends Controller
{
    public function store(TimelineEntryRequest $request): RedirectResponse
    {
        TimelineEntry::create($request->validated());

        return back()->with('success', 'Entri timeline berhasil ditambahkan.');
    }

    public function update(TimelineEntryRequest $request, TimelineEntry $timeline): RedirectResponse
    {
        $timeline->update($request->validated());

        return back()->with('success', 'Entri timeline berhasil diperbarui.');
    }

    public function destroy(TimelineEntry $timeline): RedirectResponse
    {
        $timeline->delete();

        return back()->with('success', 'Entri timeline berhasil dihapus.');
    }
}

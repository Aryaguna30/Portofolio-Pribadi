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
        TimelineEntry::create($this->prepareTranslatable($request->validated()));

        return back()->with('success', 'Entri timeline berhasil ditambahkan.');
    }

    public function update(TimelineEntryRequest $request, TimelineEntry $timeline): RedirectResponse
    {
        $data = $this->prepareTranslatable($request->validated());
        foreach (['institution', 'role', 'description'] as $field) {
            if (isset($data[$field])) {
                $timeline->setTranslation($field, app()->getLocale(), $data[$field]);
            }
        }
        $timeline->fill(array_diff_key($data, array_flip(['institution', 'role', 'description'])));
        $timeline->save();

        return back()->with('success', 'Entri timeline berhasil diperbarui.');
    }

    public function destroy(TimelineEntry $timeline): RedirectResponse
    {
        $timeline->delete();

        return back()->with('success', 'Entri timeline berhasil dihapus.');
    }

    private function prepareTranslatable(array $data): array
    {
        $locale = app()->getLocale();
        foreach (['institution', 'role', 'description'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = [$locale => $data[$field]];
            }
        }
        return $data;
    }
}

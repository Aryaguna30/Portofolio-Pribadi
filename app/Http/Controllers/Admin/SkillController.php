<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;

class SkillController extends Controller
{
    public function store(SkillRequest $request): RedirectResponse
    {
        Skill::create($request->validated());

        return back()->with('success', 'Keahlian berhasil ditambahkan.');
    }

    public function update(SkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->update($request->validated());

        return back()->with('success', 'Keahlian berhasil diperbarui.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        return back()->with('success', 'Keahlian berhasil dihapus.');
    }
}

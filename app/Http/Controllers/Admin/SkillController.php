<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillRequest;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    /**
     * Display a listing of all skills.
     *
     * Requirements: 10.8
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Skills', [
            'skills' => Skill::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created skill.
     *
     * Requirements: 10.8
     */
    public function store(SkillRequest $request): JsonResponse
    {
        $skill = Skill::create($request->validated());

        return response()->json($skill, 201);
    }

    /**
     * Display the specified skill.
     *
     * Requirements: 10.8
     */
    public function show(Skill $skill): Response
    {
        return Inertia::render('Admin/Skills', [
            'skill' => $skill,
        ]);
    }

    /**
     * Update the specified skill.
     *
     * Requirements: 10.8
     */
    public function update(SkillRequest $request, Skill $skill): JsonResponse
    {
        $skill->update($request->validated());

        return response()->json($skill);
    }

    /**
     * Remove the specified skill.
     *
     * Requirements: 10.8
     */
    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();

        return response()->json(null, 204);
    }
}

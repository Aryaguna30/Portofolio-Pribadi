<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(private FileStorageService $fileStorage) {}

    public function index(Request $request)
    {
        $query = $request->boolean('trashed')
            ? Project::withTrashed()->orderBy('sort_order')
            : Project::orderBy('sort_order');

        $projects = $query->get();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'trashed'  => $request->boolean('trashed'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Projects/Create');
    }

    public function store(ProjectRequest $request)
    {
        $data = $request->validated();

        $data['thumbnail_path'] = $this->fileStorage->storeWebP(
            $request->file('thumbnail'),
            'thumbnails'
        );

        $data['description'] = app('purifier')->purify($data['description'] ?? '');

        unset($data['thumbnail']);

        Project::create($data);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil ditambahkan.');
    }

    public function edit(Project $project)
    {
        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(ProjectRequest $request, Project $project)
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            $this->fileStorage->deleteFile($project->thumbnail_path);
            $data['thumbnail_path'] = $this->fileStorage->storeWebP(
                $request->file('thumbnail'),
                'thumbnails'
            );
        }

        $data['description'] = app('purifier')->purify($data['description'] ?? $project->description);

        unset($data['thumbnail']);

        $project->update($data);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        $this->fileStorage->deleteFile($project->thumbnail_path);
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil dihapus.');
    }
}

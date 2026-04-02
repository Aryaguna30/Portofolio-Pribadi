<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalProjects'  => Project::count(),
                'totalMessages'  => Message::count(),
                'unreadMessages' => Message::where('is_read', false)->count(),
            ],
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Message;
use App\Services\MetaTagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $meta = [];
        try {
            $meta = (new MetaTagService())->forHome();
        } catch (\Throwable $e) {
            // DB may not be ready; fall back to empty meta
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => Auth::user()
                    ? Auth::user()->only('id', 'name', 'email')
                    : null,
            ],
            'flash' => [
                'success' => session()->get('success'),
                'error'   => session()->get('error'),
            ],
            'locale'           => app()->getLocale() ?: 'id',
            'meta'             => $meta,
            'turnstileSiteKey' => config('services.turnstile.site_key') ?: null,
            // Share unread message count globally so AdminLayout badge always works
            'unreadMessages'   => fn () => Auth::check()
                ? Message::where('is_read', false)->count()
                : 0,
            'ziggy'  => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}

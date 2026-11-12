<?php

namespace App\Http\Controllers;

use App\Models\FacebookLive;
use App\Support\FacebookLinkResolver;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminLiveMassController extends Controller
{
    public function edit(): View
    {
        return view()->file(resource_path('admin/live-mass.blade.php'), [
            'facebookLive' => FacebookLive::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (is_string($request->input('url'))) {
            $request->merge(['url' => trim($request->input('url'))]);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'url' => [
                'required',
                'string',
                'max:500',
                'url:https',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (is_string($value) && ! FacebookLive::isFacebookUrl($value)) {
                        $fail(__('Please paste a Facebook link (facebook.com or fb.watch).'));
                    }
                },
            ],
            'is_live' => ['sometimes', 'boolean'],
        ], [
            'url.required' => __('Please paste the Facebook Live link.'),
            'url.url' => __('Please paste a full link that starts with https://, for example https://www.facebook.com/yourparish/live.'),
        ], [
            'url' => __('Facebook Live link'),
        ]);

        // Turn short share links into full video links so the video can play on the page.
        $validated['url'] = app(FacebookLinkResolver::class)->resolve($validated['url']);

        DB::transaction(function () use ($validated, $request) {
            // Keep only the current link.
            FacebookLive::query()->delete();

            FacebookLive::query()->create([
                'title' => filled($validated['title'] ?? null) ? trim($validated['title']) : null,
                'url' => $validated['url'],
                'is_live' => $request->boolean('is_live'),
                'posted_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('admin.live-mass')
            ->with('status', FacebookLive::isEmbeddableVideoUrl($validated['url'])
                ? __('Facebook Live link saved. Parishioners can now watch the video on their Live Mass page.')
                : __('Facebook Live link saved. This link can only open Facebook, so parishioners will see a "Watch on Facebook" button instead of the video. Paste the link of the live video itself to show it on the page.'));
    }

    public function destroy(): RedirectResponse
    {
        FacebookLive::query()->delete();

        return redirect()
            ->route('admin.live-mass')
            ->with('status', __('Facebook Live link removed. Parishioners will no longer see it.'));
    }
}

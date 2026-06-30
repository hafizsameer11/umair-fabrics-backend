<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Services\CacheService;
use App\Services\RevalidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroSlideController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order')->get();

        return view('admin.hero-slides.index', compact('slides'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = HeroSlide::max('sort_order') + 1;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['countdown_ends_at'] = $request->input('countdown_ends_at') ?: null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('hero-slides', 'public');
        }

        HeroSlide::create($data);
        $this->flush();

        return back()->with('success', 'Slide added.');
    }

    public function update(Request $request, HeroSlide $slide)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['countdown_ends_at'] = $request->input('countdown_ends_at') ?: null;

        if ($request->hasFile('image')) {
            if ($slide->image) {
                Storage::disk('public')->delete($slide->image);
            }
            $data['image'] = $request->file('image')->store('hero-slides', 'public');
        }

        $slide->update($data);
        $this->flush();

        return back()->with('success', 'Slide updated.');
    }

    public function destroy(HeroSlide $slide)
    {
        if ($slide->image) {
            Storage::disk('public')->delete($slide->image);
        }
        $slide->delete();
        $this->flush();

        return back()->with('success', 'Slide deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:4096',
            'countdown_ends_at' => 'nullable|date',
        ]);
    }

    private function flush(): void
    {
        CacheService::flushStorefront();
        $this->revalidate->revalidate(['/']);
    }
}

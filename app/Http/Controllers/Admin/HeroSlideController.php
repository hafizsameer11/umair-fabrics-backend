<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
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
        $slides = HeroSlide::with('collection')->orderBy('sort_order')->get();
        $collections = Collection::orderBy('name')->get();

        return view('admin.hero-slides.index', compact('slides', 'collections'));
    }

    public function store(Request $request)
    {
        $data = $this->prepareData($this->validated($request), $request);
        $data['sort_order'] = HeroSlide::max('sort_order') + 1;
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('hero-slides', 'public');
        }

        HeroSlide::create($data);
        $this->flush();

        return back()->with('success', 'Slide added.');
    }

    public function update(Request $request, HeroSlide $slide)
    {
        $data = $this->prepareData($this->validated($request), $request);
        $data['is_active'] = $request->boolean('is_active');

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
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'collection_id' => 'nullable|exists:collections,id',
            'image' => 'nullable|image|max:4096',
            'countdown_ends_at' => 'nullable|date',
        ]);
    }

    private function prepareData(array $data, Request $request): array
    {
        $data['title'] = $request->input('title') ?: '';
        $data['subtitle'] = $request->input('subtitle') ?: null;
        $data['button_text'] = $request->input('button_text') ?: null;
        $data['collection_id'] = $request->input('collection_id') ?: null;
        $data['countdown_ends_at'] = $request->input('countdown_ends_at') ?: null;

        if ($data['collection_id']) {
            $collection = Collection::find($data['collection_id']);
            $data['button_url'] = $collection ? '/collections/'.$collection->slug.'/' : null;
        } else {
            $data['collection_id'] = null;
            $data['button_url'] = null;
        }

        return $data;
    }

    private function flush(): void
    {
        CacheService::flushStorefront();
        $this->revalidate->revalidate(['/']);
    }
}

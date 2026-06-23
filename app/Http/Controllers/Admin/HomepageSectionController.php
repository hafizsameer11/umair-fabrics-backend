<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\HomepageSection;
use App\Services\RevalidateService;
use Illuminate\Http\Request;

class HomepageSectionController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $sections = HomepageSection::with('collection')->orderBy('sort_order')->get();

        return view('admin.homepage.index', compact('sections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'type' => 'required|in:collection,featured_products',
            'collection_id' => 'nullable|exists:collections,id',
            'product_limit' => 'integer|min:1|max:24',
            'is_active' => 'boolean',
        ]);

        HomepageSection::create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => HomepageSection::max('sort_order') + 1,
        ]);

        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Section added.');
    }

    public function update(Request $request, HomepageSection $section)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'type' => 'required|in:collection,featured_products',
            'collection_id' => 'nullable|exists:collections,id',
            'product_limit' => 'integer|min:1|max:24',
            'is_active' => 'boolean',
        ]);

        $section->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Section updated.');
    }

    public function destroy(HomepageSection $section)
    {
        $section->delete();
        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Section deleted.');
    }
}

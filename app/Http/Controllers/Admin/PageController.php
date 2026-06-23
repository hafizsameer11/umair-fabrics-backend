<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\RevalidateService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $pages = Page::orderBy('title')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page]);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->validated($request));
        $this->revalidate->revalidate(["/pages/{$page->slug}"]);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page created.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validated($request));
        $this->revalidate->revalidate(["/pages/{$page->slug}"]);

        return back()->with('success', 'Page updated.');
    }

    public function destroy(Page $page)
    {
        $slug = $page->slug;
        $page->delete();
        $this->revalidate->revalidate(["/pages/{$slug}"]);

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'body_html' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}

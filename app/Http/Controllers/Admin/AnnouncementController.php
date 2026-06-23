<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementBar;
use App\Services\RevalidateService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $announcements = AnnouncementBar::orderBy('sort_order')->get();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string',
            'link' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        AnnouncementBar::create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => AnnouncementBar::max('sort_order') + 1,
        ]);

        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Announcement added.');
    }

    public function update(Request $request, AnnouncementBar $announcement)
    {
        $data = $request->validate([
            'message' => 'required|string',
            'link' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $announcement->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Announcement updated.');
    }

    public function destroy(AnnouncementBar $announcement)
    {
        $announcement->delete();
        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Announcement deleted.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\RevalidateService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $headerMenu = Menu::with('items')->where('location', 'header')->first();
        $footerMenu = Menu::with('items')->where('location', 'footer')->first();
        $collections = Collection::orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.menus.index', compact('headerMenu', 'footerMenu', 'collections'));
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'location' => 'required|in:header,footer',
            'label' => 'required|string|max:255',
            'url' => 'nullable|string|max:500',
            'collection_id' => 'nullable|exists:collections,id',
        ]);

        if (! empty($data['collection_id'])) {
            $collection = Collection::findOrFail($data['collection_id']);
            $data['label'] = $data['label'] ?: $collection->name;
            $data['url'] = '/collections/'.$collection->slug;
        }

        if (empty($data['url'])) {
            return back()->withErrors(['url' => 'URL or collection is required.']);
        }

        $menu = Menu::firstOrCreate(
            ['location' => $data['location']],
            ['name' => ucfirst($data['location']).' Menu']
        );

        MenuItem::create([
            'label' => $data['label'],
            'url' => $data['url'],
            'menu_id' => $menu->id,
            'sort_order' => MenuItem::where('menu_id', $menu->id)->max('sort_order') + 1,
        ]);

        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Menu item added.');
    }

    public function destroyItem(MenuItem $item)
    {
        $item->delete();
        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Menu item removed.');
    }
}

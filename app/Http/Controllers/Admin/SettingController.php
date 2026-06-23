<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\RevalidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function edit()
    {
        return view('admin.settings.edit', [
            'settings' => [
                'store_name' => Setting::get('store_name', ''),
                'store_phone' => Setting::get('store_phone', ''),
                'store_email' => Setting::get('store_email', ''),
                'store_whatsapp' => Setting::get('store_whatsapp', ''),
                'store_website' => Setting::get('store_website', ''),
                'store_logo' => Setting::get('store_logo'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email',
            'store_website' => 'nullable|url|max:255',
            'store_whatsapp' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
        ]);

        Setting::set('store_name', $data['store_name']);
        Setting::set('store_phone', $data['store_phone'] ?? '');
        Setting::set('store_email', $data['store_email'] ?? '');
        Setting::set('store_website', $data['store_website'] ?? '');
        Setting::set('store_whatsapp', $data['store_whatsapp'] ?? '');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('store_logo', $path);
        }

        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Settings saved.');
    }
}

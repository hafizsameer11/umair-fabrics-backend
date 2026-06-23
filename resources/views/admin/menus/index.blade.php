@extends('layouts.admin')
@section('title', 'Menus')
@section('header', 'Navigation Menus')
@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Add menu item</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.menus.items.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Menu</label>
                        <select name="location" class="form-select">
                            <option value="header">Header menu</option>
                            <option value="footer">Footer menu</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quick add collection</label>
                        <select name="collection_id" class="form-select">
                            <option value="">— Select collection —</option>
                            @foreach($collections as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Label (optional if collection selected)</label><input type="text" name="label" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Custom URL</label><input type="text" name="url" class="form-control" placeholder="/products or /pages/contact"></div>
                    <button type="submit" class="btn btn-primary">Add item</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        @foreach([['menu' => $headerMenu, 'title' => 'Header menu'], ['menu' => $footerMenu, 'title' => 'Footer menu']] as $block)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">{{ $block['title'] }}</div>
                <ul class="list-group list-group-flush">
                    @forelse($block['menu']?->items ?? [] as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ $item->label }}</strong> <code class="small">{{ $item->url }}</code></span>
                            <form method="POST" action="{{ route('admin.menus.items.destroy', $item) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No items yet.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
</div>
@endsection

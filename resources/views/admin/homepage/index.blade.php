@extends('layouts.admin')
@section('title', 'Homepage')
@section('header', 'Homepage Sections')
@php $collections = \App\Models\Collection::orderBy('name')->get(); @endphp
@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Add section</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.homepage.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Subtitle</label><input type="text" name="subtitle" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Type</label><select name="type" class="form-select"><option value="collection">Collection</option><option value="featured_products">Featured products</option></select></div>
                    <div class="mb-3"><label class="form-label">Collection</label><select name="collection_id" class="form-select"><option value="">—</option>@foreach($collections as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label">Product limit</label><input type="number" name="product_limit" class="form-control" value="8" min="1" max="24"></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active</label></div>
                    <button type="submit" class="btn btn-primary">Add section</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        @foreach($sections as $section)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.homepage.update', $section) }}">
                        @csrf @method('PUT')
                        <input type="text" name="title" class="form-control mb-2 fw-medium" value="{{ $section->title }}">
                        <input type="text" name="subtitle" class="form-control mb-2" value="{{ $section->subtitle }}" placeholder="Subtitle">
                        <div class="row g-2 mb-2">
                            <div class="col-6"><select name="type" class="form-select form-select-sm"><option value="collection" @selected($section->type==='collection')>Collection</option><option value="featured_products" @selected($section->type==='featured_products')>Featured</option></select></div>
                            <div class="col-6"><select name="collection_id" class="form-select form-select-sm"><option value="">—</option>@foreach($collections as $c)<option value="{{ $c->id }}" @selected($section->collection_id==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
                        </div>
                        <input type="number" name="product_limit" class="form-control form-control-sm mb-2" value="{{ $section->product_limit }}">
                        <div class="d-flex justify-content-between"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($section->is_active)><label class="form-check-label">Active</label></div><button type="submit" class="btn btn-sm btn-primary">Save</button></div>
                    </form>
                    <form method="POST" action="{{ route('admin.homepage.destroy', $section) }}" class="mt-2 text-end">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

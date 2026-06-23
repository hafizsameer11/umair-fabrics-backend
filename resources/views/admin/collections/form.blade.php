@extends('layouts.admin')
@section('title', $collection->exists ? 'Edit Collection' : 'Add Collection')
@section('header', $collection->exists ? 'Edit Collection' : 'Add Collection')
@section('content')
<form method="POST" action="{{ $collection->exists ? route('admin.collections.update', $collection) : route('admin.collections.store') }}" enctype="multipart/form-data">
    @csrf
    @if($collection->exists) @method('PUT') @endif
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-medium">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $collection->name) }}" required></div>
                    <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $collection->slug) }}"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $collection->description) }}</textarea></div>
                    <div class="mb-3"><label class="form-label">Image</label>
                        @if($collection->image)<img src="{{ asset('storage/'.$collection->image) }}" class="d-block mb-2 rounded" style="max-height:120px">@endif
                        <input type="file" name="image" class="form-control" accept="image/*"></div>
                    <div class="mb-3"><label class="form-label">Homepage title</label><input type="text" name="homepage_title" class="form-control" value="{{ old('homepage_title', $collection->homepage_title) }}"></div>
                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="show_on_homepage" value="0">
                        <input class="form-check-input" type="checkbox" name="show_on_homepage" value="1" id="show_hp" @checked(old('show_on_homepage', $collection->show_on_homepage))>
                        <label class="form-check-label" for="show_hp">Show on homepage</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Products in this collection</label>
                        <div class="border rounded p-3" style="max-height:280px;overflow-y:auto;">
                            @foreach($products as $product)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="products[]" value="{{ $product->id }}" id="p{{ $product->id }}" @checked($collection->products->contains($product->id))>
                                    <label class="form-check-label" for="p{{ $product->id }}">{{ $product->title }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Collection</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

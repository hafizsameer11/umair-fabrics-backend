@extends('layouts.admin')
@section('title', $bundle->exists ? 'Edit Bundle' : 'New Bundle')
@section('header', $bundle->exists ? 'Edit Bundle' : 'New Bundle')
@section('content')
<form method="POST" action="{{ $bundle->exists ? route('admin.bundles.update', $bundle) : route('admin.bundles.store') }}" enctype="multipart/form-data" class="row g-4">
    @csrf
    @if($bundle->exists) @method('PUT') @endif
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{ old('title', $bundle->title) }}" required></div>
                <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $bundle->slug) }}" placeholder="auto-generated if empty"></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description', $bundle->description) }}</textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Discount %</label><input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', $bundle->discount_percent ?? 10) }}" min="0" max="100" step="0.01" required></div>
                    <div class="col-md-6 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $bundle->is_active ?? true))><label class="form-check-label">Active</label></div></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Bundle image</label><input type="file" name="image" class="form-control" accept="image/*">@if($bundle->image)<img src="{{ asset('storage/'.$bundle->image) }}" class="mt-2 rounded" style="max-height:120px">@endif</div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Products in bundle</div>
            <div class="card-body" style="max-height:420px;overflow-y:auto">
                @php $selected = old('product_ids', $bundle->products->pluck('id')->all()); @endphp
                @foreach($products as $product)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="product_ids[]" value="{{ $product->id }}" id="p{{ $product->id }}" @checked(in_array($product->id, $selected))>
                        <label class="form-check-label" for="p{{ $product->id }}">{{ $product->title }}</label>
                    </div>
                @endforeach
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-3">Save bundle</button>
        <a href="{{ route('admin.bundles.index') }}" class="btn btn-outline-secondary w-100 mt-2">Back</a>
    </div>
</form>
@endsection

@extends('layouts.admin')
@section('title', 'Hero Slides')
@section('header', 'Hero Slides')
@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Add slide</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.hero-slides.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <p class="small text-muted mb-0 mt-1">Wide banner works best — e.g. 1920×800px.</p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Link to collection</label>
                        <select name="collection_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($collections as $collection)
                                <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                            @endforeach
                        </select>
                        <p class="small text-muted mb-0 mt-1">Visitors go to this collection when they click the slide or button.</p>
                    </div>
                    <hr class="my-3">
                    <p class="small text-muted">All fields below are optional. Leave them empty for an image-only banner.</p>
                    <div class="mb-2"><label class="form-label">Title <span class="text-muted fw-normal">(optional)</span></label><input type="text" name="title" class="form-control"></div>
                    <div class="mb-2"><label class="form-label">Subtitle <span class="text-muted fw-normal">(optional)</span></label><input type="text" name="subtitle" class="form-control"></div>
                    <div class="mb-2"><label class="form-label">Button text <span class="text-muted fw-normal">(optional)</span></label><input type="text" name="button_text" class="form-control" placeholder="Shop now"></div>
                    <div class="mb-2"><label class="form-label">Countdown ends <span class="text-muted fw-normal">(optional)</span></label><input type="datetime-local" name="countdown_ends_at" class="form-control"></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active</label></div>
                    <button type="submit" class="btn btn-primary">Add slide</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        @foreach($slides as $slide)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.hero-slides.update', $slide) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        @if($slide->image)
                            <img src="{{ asset('storage/'.$slide->image) }}" class="mb-3 rounded w-100" style="max-height:120px;object-fit:cover" alt="">
                        @endif
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Replace image</label>
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-0">Collection</label>
                                <select name="collection_id" class="form-select form-select-sm">
                                    <option value="">— None —</option>
                                    @foreach($collections as $collection)
                                        <option value="{{ $collection->id }}" @selected($slide->collection_id == $collection->id)>{{ $collection->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6"><input type="text" name="title" class="form-control form-control-sm" value="{{ $slide->title }}" placeholder="Title (optional)"></div>
                            <div class="col-md-6"><input type="text" name="subtitle" class="form-control form-control-sm" value="{{ $slide->subtitle }}" placeholder="Subtitle (optional)"></div>
                            <div class="col-md-6"><input type="text" name="button_text" class="form-control form-control-sm" value="{{ $slide->button_text }}" placeholder="Button text (optional)"></div>
                            <div class="col-md-6"><input type="datetime-local" name="countdown_ends_at" class="form-control form-control-sm" value="{{ $slide->countdown_ends_at?->format('Y-m-d\TH:i') }}" placeholder="Countdown end"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($slide->is_active)><label class="form-check-label">Active</label></div>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" class="mt-2 text-end">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </div>
            </div>
        @endforeach
        @if($slides->isEmpty())<div class="alert alert-info">No slides yet. Upload a banner image above to get started.</div>@endif
    </div>
</div>
@endsection

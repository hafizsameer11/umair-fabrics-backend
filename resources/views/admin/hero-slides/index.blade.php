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
                    <div class="mb-2"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Subtitle</label><input type="text" name="subtitle" class="form-control"></div>
                    <div class="mb-2"><label class="form-label">Button text</label><input type="text" name="button_text" class="form-control" placeholder="Shop Now"></div>
                    <div class="mb-2"><label class="form-label">Button URL</label><input type="text" name="button_url" class="form-control" placeholder="/products"></div>
                    <div class="mb-2"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                    <p class="small text-muted mb-2">Wide banner works best — e.g. 1920×800px. Leave title empty if text is baked into the image.</p>
                    <div class="mb-2"><label class="form-label">Countdown ends (optional)</label><input type="datetime-local" name="countdown_ends_at" class="form-control"></div>
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
                        <div class="row g-2">
                            <div class="col-md-6"><input type="text" name="title" class="form-control" value="{{ $slide->title }}" required></div>
                            <div class="col-md-6"><input type="text" name="subtitle" class="form-control" value="{{ $slide->subtitle }}" placeholder="Subtitle"></div>
                            <div class="col-md-4"><input type="text" name="button_text" class="form-control" value="{{ $slide->button_text }}" placeholder="Button text"></div>
                            <div class="col-md-4"><input type="text" name="button_url" class="form-control" value="{{ $slide->button_url }}" placeholder="Button URL"></div>
                            <div class="col-md-4"><input type="file" name="image" class="form-control form-control-sm" accept="image/*"></div>
                            <div class="col-md-4"><input type="datetime-local" name="countdown_ends_at" class="form-control form-control-sm" value="{{ $slide->countdown_ends_at?->format('Y-m-d\TH:i') }}" placeholder="Countdown end"></div>
                        </div>
                        @if($slide->image)<img src="{{ asset('storage/'.$slide->image) }}" class="mt-2 rounded" style="max-height:80px">@endif
                        <div class="d-flex justify-content-between mt-2">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($slide->is_active)><label class="form-check-label">Active</label></div>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" class="mt-2 text-end">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </div>
            </div>
        @endforeach
        @if($slides->isEmpty())<div class="alert alert-info">No slides yet. Default slides are seeded on first setup.</div>@endif
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', $page->exists ? 'Edit Page' : 'New Page')
@section('header', $page->exists ? 'Edit Page' : 'New Page')
@push('styles')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
@endpush
@section('content')
<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
    @csrf
    @if($page->exists) @method('PUT') @endif
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{ old('title', $page->title) }}" required></div>
            <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}" placeholder="about-us"></div>
            <div class="mb-3"><label class="form-label">Content</label><textarea name="body_html" id="body_html" class="form-control" rows="12">{{ old('body_html', $page->body_html) }}</textarea></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Meta title</label><input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $page->meta_title) }}"></div>
                <div class="col-md-6"><label class="form-label">Meta description</label><input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $page->meta_description) }}"></div>
            </div>
            <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published ?? true))><label class="form-check-label">Published</label></div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Save page</button>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Back</a>
</form>
@endsection
@push('scripts')
<script>ClassicEditor.create(document.querySelector('#body_html')).catch(()=>{});</script>
@endpush

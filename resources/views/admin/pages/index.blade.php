@extends('layouts.admin')
@section('title', 'Pages')
@section('header', 'CMS Pages')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <p class="text-muted mb-0">About, Contact, and other static pages.</p>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">New page</a>
</div>
<div class="card border-0 shadow-sm">
    <ul class="list-group list-group-flush">
        @forelse($pages as $page)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $page->title }}</strong>
                    <code class="small ms-2">/pages/{{ $page->slug }}</code>
                    @if($page->is_published)<span class="badge bg-success ms-2">Published</span>@else<span class="badge bg-secondary ms-2">Draft</span>@endif
                </div>
                <div>
                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </div>
            </li>
        @empty
            <li class="list-group-item text-muted">No pages yet.</li>
        @endforelse
    </ul>
</div>
@endsection

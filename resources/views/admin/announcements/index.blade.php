@extends('layouts.admin')
@section('title', 'Announcements')
@section('header', 'Announcement Bar')
@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Add announcement</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.announcements.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="2" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Link (optional)</label><input type="url" name="link" class="form-control"></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active</label></div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        @foreach($announcements as $a)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.announcements.update', $a) }}">
                        @csrf @method('PUT')
                        <textarea name="message" class="form-control mb-2" rows="2">{{ $a->message }}</textarea>
                        <input type="url" name="link" class="form-control mb-2" value="{{ $a->link }}" placeholder="Optional link">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($a->is_active)><label class="form-check-label">Active</label></div>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" class="mt-2 text-end">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

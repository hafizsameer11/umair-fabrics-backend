@extends('layouts.admin')
@section('title', 'Collections')
@section('header', 'Collections')
@section('content')
<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('admin.collections.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Collection</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Products</th><th>Homepage</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @foreach($collections as $collection)
                    <tr>
                        <td class="fw-medium">{{ $collection->name }}</td>
                        <td>{{ $collection->products_count }}</td>
                        <td>@if($collection->show_on_homepage)<span class="badge bg-success">Yes</span>@else<span class="badge bg-secondary">No</span>@endif</td>
                        <td class="text-end">
                            <a href="{{ route('admin.collections.edit', $collection) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.collections.destroy', $collection) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Bundles')
@section('header', 'Product Bundles')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Create bundles with multiple products and a discount.</p>
    <a href="{{ route('admin.bundles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New bundle</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Bundle</th><th>Products</th><th>Discount</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($bundles as $bundle)
                    <tr>
                        <td><strong>{{ $bundle->title }}</strong><br><code class="small">{{ $bundle->slug }}</code></td>
                        <td>{{ $bundle->products_count }}</td>
                        <td>{{ $bundle->discount_percent }}%</td>
                        <td><span class="badge {{ $bundle->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $bundle->is_active ? 'Active' : 'Hidden' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.bundles.edit', $bundle) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.bundles.destroy', $bundle) }}" class="d-inline" onsubmit="return confirm('Delete bundle?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted p-4">No bundles yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

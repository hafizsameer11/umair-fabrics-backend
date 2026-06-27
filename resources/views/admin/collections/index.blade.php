@extends('layouts.admin')
@section('title', 'Collections')
@section('header', 'Collections')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0 small">Homepage sections are built from collections marked <strong>Show on homepage</strong>. Drag rows to change homepage order.</p>
    <a href="{{ route('admin.collections.create') }}" class="btn btn-primary flex-shrink-0"><i class="bi bi-plus-lg me-1"></i> Add Collection</a>
</div>

<form method="POST" action="{{ route('admin.collections.reorder') }}" id="reorder-form">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:2.5rem"></th>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Homepage</th>
                        <th>Max on home</th>
                        <th>Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="collection-rows">
                    @foreach($collections as $collection)
                        <tr draggable="true" data-id="{{ $collection->id }}" class="collection-row">
                            <td class="text-muted"><i class="bi bi-grip-vertical"></i></td>
                            <td class="fw-medium">{{ $collection->name }}</td>
                            <td>{{ $collection->products_count }}</td>
                            <td>
                                @if($collection->show_on_homepage)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>{{ $collection->homepage_product_limit ?? 8 }}</td>
                            <td>{{ $collection->sort_order }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.collections.edit', $collection) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.collections.destroy', $collection) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($collections->isNotEmpty())
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-outline-primary btn-sm">Save homepage order</button>
            </div>
        @endif
    </div>
</form>

@push('scripts')
<script>
(function () {
    const tbody = document.getElementById('collection-rows');
    const form = document.getElementById('reorder-form');
    if (!tbody || !form) return;

    let dragRow = null;

    tbody.querySelectorAll('.collection-row').forEach(row => {
        row.addEventListener('dragstart', () => { dragRow = row; row.classList.add('table-active'); });
        row.addEventListener('dragend', () => { row.classList.remove('table-active'); dragRow = null; });
        row.addEventListener('dragover', e => e.preventDefault());
        row.addEventListener('drop', e => {
            e.preventDefault();
            if (!dragRow || dragRow === row) return;
            const rect = row.getBoundingClientRect();
            const after = (e.clientY - rect.top) > rect.height / 2;
            if (after) row.after(dragRow); else row.before(dragRow);
        });
    });

    form.addEventListener('submit', e => {
        form.querySelectorAll('input[name="order[]"]').forEach(i => i.remove());
        tbody.querySelectorAll('.collection-row').forEach(row => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order[]';
            input.value = row.dataset.id;
            form.appendChild(input);
        });
    });
})();
</script>
@endpush
@endsection

@extends('layouts.admin')
@section('title', 'Products')
@section('header', 'Products')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <form method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 400px;">
        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search products...">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px"></th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    @php
                        $totalStock = $product->variants->sum('stock');
                        $isActive = $product->status === 'active';
                    @endphp
                    <tr>
                        <td>
                            @if($product->images->first())
                                <img src="{{ $product->images->first()->url() }}" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="bi bi-image text-muted"></i></div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.products.edit', $product) }}" class="fw-medium text-decoration-none">{{ $product->title }}</a>
                            <div class="small text-muted">Min qty: {{ $product->min_order_qty }}</div>
                        </td>
                        <td>
                            <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $isActive ? 'btn-success' : 'btn-outline-secondary' }}" title="Click to {{ $isActive ? 'deactivate' : 'activate' }}">
                                    <i class="bi {{ $isActive ? 'bi-check-circle' : 'bi-pause-circle' }} me-1"></i>
                                    {{ $isActive ? 'Active' : 'Draft' }}
                                </button>
                            </form>
                        </td>
                        <td>Rs.{{ number_format($product->minPrice()) }}</td>
                        <td>
                            @if($totalStock <= 0)
                                <span class="badge bg-danger">Out of stock</span>
                            @elseif($totalStock < 5)
                                <span class="badge bg-warning text-dark">{{ $totalStock }} left</span>
                            @else
                                <span class="text-success">{{ $totalStock }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.products.duplicate', $product) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-secondary" title="Duplicate"><i class="bi bi-copy"></i></button></form>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection
